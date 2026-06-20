<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class PhaseThreeDomainSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $hotelIds = $this->seedHotels($now);
        $hotelId = $hotelIds['ORIA'];

        DB::table('users')
            ->whereNull('hotel_id')
            ->where('username', '!=', 'superadmin')
            ->update(['hotel_id' => $hotelId]);

        $this->seedHotelRolesAndUsers($hotelIds);
        $this->seedHotelOperationalData($hotelIds, $now);

        foreach (DB::table('m_meeting_rooms')->get() as $room) {
            DB::table('meeting_rooms')->updateOrInsert(
                ['hotel_id' => $hotelId, 'code' => $room->kd_room],
                [
                    'name' => $room->name,
                    'operational_status' => $this->roomStatus($room->room_availability),
                    'facilities' => json_encode(['legacy_room_code' => $room->kd_room]),
                    'created_at' => $room->created_at ?? $now,
                    'updated_at' => $room->updated_at ?? $now,
                ]
            );
        }

        foreach (DB::table('m_client')->get() as $client) {
            DB::table('clients')->updateOrInsert(
                ['hotel_id' => $hotelId, 'external_id' => $client->code],
                [
                    'company_name' => $client->name,
                    'contact_name' => $client->contact_person,
                    'contact_email' => $client->email ? mb_strtolower(trim($client->email)) : null,
                    'contact_phone' => $client->company_phone,
                    'metadata' => json_encode(['legacy_client_code' => $client->code]),
                    'created_at' => $client->created_at ?? $now,
                    'updated_at' => $client->updated_at ?? $now,
                ]
            );
        }

        foreach (DB::table('m_packages')->get() as $package) {
            DB::table('meeting_packages')->updateOrInsert(
                ['hotel_id' => $hotelId, 'code' => $package->kd_pck],
                [
                    'name' => $package->name,
                    'description' => $package->details,
                    'price' => $package->price,
                    'is_active' => true,
                    'metadata' => json_encode(['legacy_count_qr' => $package->count_qr]),
                    'created_at' => $package->created_at ?? $now,
                    'updated_at' => $package->updated_at ?? $now,
                ]
            );

            $packageId = DB::table('meeting_packages')->where('hotel_id', $hotelId)->where('code', $package->kd_pck)->value('id');
            DB::table('package_entitlements')->updateOrInsert(
                ['package_id' => $packageId, 'entitlement_type' => 'CUSTOM'],
                [
                    'quantity' => max(1, (int) $package->count_qr),
                    'metadata' => json_encode(['legacy_field' => 'count_qr']),
                    'created_at' => $package->created_at ?? $now,
                    'updated_at' => $package->updated_at ?? $now,
                ]
            );
        }

        foreach (DB::table('trx_meeting_schedule')->get() as $schedule) {
            $clientId = DB::table('clients')->where('hotel_id', $hotelId)->where('external_id', $schedule->code_client)->value('id');
            $roomId = DB::table('meeting_rooms')->where('hotel_id', $hotelId)->where('code', $schedule->room)->value('id');
            $packageId = DB::table('meeting_packages')->where('hotel_id', $hotelId)->where('code', $schedule->package)->value('id');
            $eventDate = $schedule->tgl_start ?? $schedule->tgl_meeting ?? now()->toDateString();
            $startAt = Carbon::parse($eventDate.' '.$schedule->jam_mulai);
            $endDate = $schedule->tgl_end ?? $eventDate;
            $endAt = Carbon::parse($endDate.' '.$schedule->jam_selesai);

            DB::table('bookings')->updateOrInsert(
                ['hotel_id' => $hotelId, 'booking_number' => $schedule->trx_number],
                [
                    'client_id' => $clientId,
                    'booking_source' => 'LEGACY',
                    'booking_date' => $eventDate,
                    'status' => 'CONFIRMED',
                    'notes' => 'Generated from legacy meeting schedule.',
                    'created_at' => $schedule->created_at ?? $now,
                    'updated_at' => $schedule->updated_at ?? $now,
                ]
            );

            $bookingId = DB::table('bookings')->where('hotel_id', $hotelId)->where('booking_number', $schedule->trx_number)->value('id');
            DB::table('meeting_events')->updateOrInsert(
                ['hotel_id' => $hotelId, 'legacy_trx_number' => $schedule->trx_number],
                [
                    'booking_id' => $bookingId,
                    'meeting_room_id' => $roomId,
                    'event_name' => 'Meeting '.$schedule->trx_number,
                    'event_date' => $eventDate,
                    'start_at' => $startAt,
                    'end_at' => $endAt,
                    'expected_participants' => max(0, (int) $schedule->kuota),
                    'actual_participants' => 0,
                    'status' => 'SCHEDULED',
                    'meeting_qr_token_hash' => null,
                    'created_at' => $schedule->created_at ?? $now,
                    'updated_at' => $schedule->updated_at ?? $now,
                ]
            );

            if ($packageId) {
                $eventId = DB::table('meeting_events')->where('hotel_id', $hotelId)->where('legacy_trx_number', $schedule->trx_number)->value('id');
                DB::table('meeting_package_assignments')->updateOrInsert(
                    ['meeting_event_id' => $eventId, 'package_id' => $packageId],
                    [
                        'participant_quota' => max(0, (int) $schedule->kuota),
                        'unit_price' => DB::table('meeting_packages')->where('id', $packageId)->value('price') ?? 0,
                        'notes' => 'Migrated from legacy package field.',
                        'created_at' => $schedule->created_at ?? $now,
                        'updated_at' => $schedule->updated_at ?? $now,
                    ]
                );
            }
        }

        foreach (DB::table('trx_meeting_attendance')->get() as $attendance) {
            $event = DB::table('meeting_events')->where('hotel_id', $hotelId)->where('legacy_trx_number', $attendance->trx_metting_number)->first();

            if (! $event) {
                continue;
            }

            $participantNumber = $attendance->trx_metting_number.'-LEGACY-'.$attendance->id;
            DB::table('participants')->updateOrInsert(
                ['meeting_event_id' => $event->id, 'participant_number' => $participantNumber],
                [
                    'hotel_id' => $hotelId,
                    'full_name' => $attendance->name,
                    'company_name' => $attendance->company ?? null,
                    'phone' => $attendance->phone_number ?? null,
                    'normalized_phone' => isset($attendance->phone_number) ? preg_replace('/[^0-9+]/', '', $attendance->phone_number) : null,
                    'registration_source' => 'LEGACY',
                    'status' => ((int) $attendance->scanned_qr > 0) ? 'CHECKED_IN' : 'REGISTERED',
                    'registered_at' => $attendance->created_at ?? $now,
                    'checked_in_at' => ((int) $attendance->scanned_qr > 0) ? ($attendance->updated_at ?? $attendance->created_at ?? $now) : null,
                    'metadata' => json_encode(['legacy_attendance_id' => $attendance->id, 'legacy_fingerprint' => $attendance->mac_address ?? null]),
                    'created_at' => $attendance->created_at ?? $now,
                    'updated_at' => $attendance->updated_at ?? $now,
                ]
            );

            if ((int) $attendance->scanned_qr > 0) {
                $participantId = DB::table('participants')->where('meeting_event_id', $event->id)->where('participant_number', $participantNumber)->value('id');
                DB::table('meeting_attendances')->updateOrInsert(
                    ['participant_id' => $participantId, 'attendance_type' => 'MEETING_CHECKIN'],
                    [
                        'meeting_event_id' => $event->id,
                        'attended_at' => $attendance->updated_at ?? $attendance->created_at ?? $now,
                        'verification_method' => 'LEGACY_QR',
                        'metadata' => json_encode(['legacy_attendance_id' => $attendance->id]),
                        'created_at' => $attendance->created_at ?? $now,
                    ]
                );
            }
        }
    }

    private function roomStatus(?string $status): string
    {
        return match ($status) {
            '001', 'AVAILABLE', 'Available' => 'AVAILABLE',
            '002', 'RESERVED', 'Booked' => 'RESERVED',
            '003', 'OCCUPIED', 'Occupied' => 'OCCUPIED',
            'CLEANING' => 'CLEANING',
            'MAINTENANCE' => 'MAINTENANCE',
            'INACTIVE' => 'INACTIVE',
            default => 'AVAILABLE',
        };
    }

    private function seedHotels($now): array
    {
        $hotels = [
            'ORIA' => [
                'name' => 'Oria Hotel Jakarta',
                'address' => 'Jl. K.H. Wahid Hasyim No. 85, Jakarta Pusat 10350, Indonesia',
                'settings' => [
                    'source' => 'https://oriahotel.com/',
                    'district' => 'Menteng / Thamrin',
                    'meeting_profile' => 'Central Jakarta business hotel',
                ],
            ],
            'ASHLEY-WH' => [
                'name' => 'Ashley Hotel Wahid Hasyim',
                'address' => 'Jl. K.H. Wahid Hasyim No. 73-75, Menteng, Jakarta Pusat 10350, Indonesia',
                'settings' => [
                    'source' => 'https://www.travelweekly.com/Hotels/Jakarta/Ashley-Hotel-Jakarta-p52827525',
                    'district' => 'Menteng',
                    'meeting_profile' => 'Nearby Wahid Hasyim hotel',
                ],
            ],
            'AONE-WH' => [
                'name' => 'AONE Hotel Jakarta',
                'address' => 'Jl. K.H. Wahid Hasyim No. 80, Jakarta Pusat 10340, Indonesia',
                'settings' => [
                    'source' => 'https://a-one-jakarta.hotels-jakarta.com/en/',
                    'district' => 'Menteng',
                    'meeting_profile' => 'Wahid Hasyim business hotel',
                ],
            ],
            'MORRISSEY' => [
                'name' => 'Morrissey Hotel Residences',
                'address' => 'Jl. K.H. Wahid Hasyim No. 70, Kb. Sirih, Menteng, Jakarta Pusat 10340, Indonesia',
                'settings' => [
                    'source' => 'https://iammorrissey.co/contact',
                    'district' => 'Menteng',
                    'meeting_profile' => 'Serviced residence with meeting/events profile',
                ],
            ],
        ];

        foreach ($hotels as $code => $hotel) {
            DB::table('hotels')->updateOrInsert(
                ['code' => $code],
                [
                    'name' => $hotel['name'],
                    'address' => $hotel['address'],
                    'timezone' => 'Asia/Jakarta',
                    'status' => 'ACTIVE',
                    'settings' => json_encode($hotel['settings']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        return DB::table('hotels')->whereIn('code', array_keys($hotels))->pluck('id', 'code')->all();
    }

    private function seedHotelRolesAndUsers(array $hotelIds): void
    {
        $rolePermissions = [
            'General Manager' => [
                'Dashboard',
                'Master Data',
                'Transaction',
                'Report',
                'Meeting Room',
                'Client',
                'Booking',
                'Meeting Event',
                'Meeting Package',
                'Participant',
                'Meeting Attendance',
                'meeting.qr.manage',
                'participant.qr.manage',
                'meal_session.view',
                'meal_session.manage',
                'redemption.view',
                'redemption.scan',
                'redemption.override',
                'redemption.reverse',
                'audit.view',
                'user.manage',
                'settings.manage',
                'meeting_room.view',
                'meeting_room.manage',
                'client.view',
                'client.manage',
                'booking.view',
                'booking.create',
                'booking.update',
                'booking.cancel',
                'meeting.view',
                'meeting.create',
                'meeting.update',
                'meeting.assign_room',
                'meeting.start',
                'meeting.complete',
                'meeting.cancel',
                'participant.view',
                'participant.register',
                'participant.update',
                'participant.block',
                'attendance.view',
                'attendance.scan',
                'meal_package.view',
                'meal_package.manage',
            ],
            'Hotel Admin' => [
                'Dashboard',
                'Master Data',
                'Transaction',
                'Meeting Room',
                'Client',
                'Booking',
                'Meeting Event',
                'Meeting Package',
                'Participant',
                'Meeting Attendance',
                'meeting.qr.manage',
                'participant.qr.manage',
                'meal_session.view',
                'meal_session.manage',
                'redemption.view',
                'redemption.scan',
                'redemption.override',
                'redemption.reverse',
                'audit.view',
                'user.manage',
                'settings.manage',
                'meeting_room.view',
                'meeting_room.manage',
                'client.view',
                'client.manage',
                'booking.view',
                'booking.create',
                'booking.update',
                'booking.cancel',
                'meeting.view',
                'meeting.create',
                'meeting.update',
                'meeting.assign_room',
                'meeting.start',
                'meeting.complete',
                'meeting.cancel',
                'participant.view',
                'participant.register',
                'participant.update',
                'participant.block',
                'attendance.view',
                'attendance.scan',
                'meal_package.view',
                'meal_package.manage',
            ],
            'Front Office' => [
                'Dashboard',
                'Transaction',
                'Meeting Event',
                'Participant',
                'Meeting Attendance',
                'Meeting Trans',
                'meal_session.view',
                'redemption.scan',
                'meeting.view',
                'participant.view',
                'participant.register',
                'participant.update',
                'attendance.view',
                'attendance.scan',
                'redemption.view',
            ],
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($permissions);
        }

        foreach ($hotelIds as $hotelCode => $hotelId) {
            $slug = str($hotelCode)->lower()->replace('-', '');
            $users = [
                [
                    'role' => 'General Manager',
                    'username' => $slug.'.gm',
                    'name' => $this->hotelShortName($hotelCode).' General Manager',
                    'email' => $slug.'.gm@headcounter.test',
                ],
                [
                    'role' => 'Hotel Admin',
                    'username' => $slug.'.admin',
                    'name' => $this->hotelShortName($hotelCode).' Hotel Admin',
                    'email' => $slug.'.admin@headcounter.test',
                ],
                [
                    'role' => 'Front Office',
                    'username' => $slug.'.fo',
                    'name' => $this->hotelShortName($hotelCode).' Front Office',
                    'email' => $slug.'.fo@headcounter.test',
                ],
            ];

            foreach ($users as $seedUser) {
                $user = User::updateOrCreate(
                    ['username' => $seedUser['username']],
                    [
                        'hotel_id' => $hotelId,
                        'name' => $seedUser['name'],
                        'email' => $seedUser['email'],
                        'password' => Hash::make('password123456'),
                    ]
                );

                $user->syncRoles([$seedUser['role']]);
            }
        }
    }

    private function seedHotelOperationalData(array $hotelIds, $now): void
    {
        foreach ($hotelIds as $hotelCode => $hotelId) {
            $this->seedRoomsForHotel($hotelId, $hotelCode, $now);
            $this->seedPackagesForHotel($hotelId, $hotelCode, $now);
            $this->seedClientsBookingsMeetingsForHotel($hotelId, $hotelCode, $now);
        }
    }

    private function seedRoomsForHotel(int $hotelId, string $hotelCode, $now): void
    {
        $rooms = [
            ['code' => 'BALLROOM', 'name' => 'Grand Ballroom', 'floor' => 'Mezzanine', 'capacity' => 180],
            ['code' => 'MEETING-1', 'name' => 'Meeting Room 1', 'floor' => '2', 'capacity' => 40],
            ['code' => 'BOARDROOM', 'name' => 'Executive Boardroom', 'floor' => '2', 'capacity' => 16],
        ];

        foreach ($rooms as $room) {
            DB::table('meeting_rooms')->updateOrInsert(
                ['hotel_id' => $hotelId, 'code' => $room['code']],
                [
                    'name' => $room['name'],
                    'floor' => $room['floor'],
                    'capacity' => $room['capacity'],
                    'operational_status' => 'AVAILABLE',
                    'facilities' => json_encode([
                        'projector' => true,
                        'sound_system' => $room['code'] === 'BALLROOM',
                        'wifi' => true,
                        'seed_hotel_code' => $hotelCode,
                    ]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    private function seedPackagesForHotel(int $hotelId, string $hotelCode, $now): void
    {
        $packages = [
            ['code' => 'HALF-DAY', 'name' => 'Half Day Meeting Package', 'price' => 250000, 'quantity' => 1],
            ['code' => 'FULL-DAY', 'name' => 'Full Day Meeting Package', 'price' => 450000, 'quantity' => 2],
        ];

        foreach ($packages as $package) {
            DB::table('meeting_packages')->updateOrInsert(
                ['hotel_id' => $hotelId, 'code' => $package['code']],
                [
                    'name' => $package['name'],
                    'description' => 'Seeded meeting package for '.$this->hotelShortName($hotelCode),
                    'price' => $package['price'],
                    'is_active' => true,
                    'metadata' => json_encode(['seed_hotel_code' => $hotelCode]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $packageId = DB::table('meeting_packages')->where('hotel_id', $hotelId)->where('code', $package['code'])->value('id');
            DB::table('package_entitlements')->updateOrInsert(
                ['package_id' => $packageId, 'entitlement_type' => 'COFFEE_BREAK'],
                [
                    'quantity' => $package['quantity'],
                    'metadata' => json_encode(['seeded_definition' => true]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    private function seedClientsBookingsMeetingsForHotel(int $hotelId, string $hotelCode, $now): void
    {
        $clientCode = $hotelCode.'-BNI';
        DB::table('clients')->updateOrInsert(
            ['hotel_id' => $hotelId, 'external_id' => $clientCode],
            [
                'company_name' => 'PT Bank Negara Indonesia Tbk',
                'contact_name' => 'Corporate Secretary Team',
                'contact_email' => strtolower($hotelCode).'.bni@example.test',
                'contact_phone' => '+62 21 2511946',
                'billing_address' => 'Jakarta Pusat',
                'metadata' => json_encode(['seed_client' => true]),
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $clientId = DB::table('clients')->where('hotel_id', $hotelId)->where('external_id', $clientCode)->value('id');
        $roomId = DB::table('meeting_rooms')->where('hotel_id', $hotelId)->where('code', 'MEETING-1')->value('id');
        $packageId = DB::table('meeting_packages')->where('hotel_id', $hotelId)->where('code', 'HALF-DAY')->value('id');
        $bookingNumber = $hotelCode.'-BKG-2026-001';

        DB::table('bookings')->updateOrInsert(
            ['hotel_id' => $hotelId, 'booking_number' => $bookingNumber],
            [
                'client_id' => $clientId,
                'booking_source' => 'DIRECT',
                'booking_date' => '2026-07-15',
                'status' => 'CONFIRMED',
                'notes' => 'Seeded Phase 3 tenant booking.',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $bookingId = DB::table('bookings')->where('hotel_id', $hotelId)->where('booking_number', $bookingNumber)->value('id');
        DB::table('meeting_events')->updateOrInsert(
            ['hotel_id' => $hotelId, 'legacy_trx_number' => $hotelCode.'-MTG-2026-001'],
            [
                'booking_id' => $bookingId,
                'meeting_room_id' => $roomId,
                'event_name' => $this->hotelShortName($hotelCode).' Corporate Briefing',
                'event_date' => '2026-07-15',
                'start_at' => '2026-07-15 09:00:00+07',
                'end_at' => '2026-07-15 11:00:00+07',
                'expected_participants' => 25,
                'actual_participants' => 1,
                'status' => 'SCHEDULED',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $eventId = DB::table('meeting_events')->where('hotel_id', $hotelId)->where('legacy_trx_number', $hotelCode.'-MTG-2026-001')->value('id');
        DB::table('meeting_package_assignments')->updateOrInsert(
            ['meeting_event_id' => $eventId, 'package_id' => $packageId],
            [
                'participant_quota' => 25,
                'unit_price' => DB::table('meeting_packages')->where('id', $packageId)->value('price') ?? 0,
                'notes' => 'Seeded package assignment.',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('participants')->updateOrInsert(
            ['meeting_event_id' => $eventId, 'participant_number' => $hotelCode.'-P-0001'],
            [
                'hotel_id' => $hotelId,
                'full_name' => 'Andi Pratama',
                'company_name' => 'PT Bank Negara Indonesia Tbk',
                'email' => strtolower($hotelCode).'.andi@example.test',
                'normalized_email' => strtolower($hotelCode).'.andi@example.test',
                'phone' => '+62 812 1000 0001',
                'normalized_phone' => '+6281210000001',
                'identity_reference' => $hotelCode.'-KTP-0001',
                'registration_source' => 'SEEDED',
                'status' => 'REGISTERED',
                'registered_at' => $now,
                'metadata' => json_encode(['seeded_participant' => true]),
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $participantId = DB::table('participants')->where('meeting_event_id', $eventId)->where('participant_number', $hotelCode.'-P-0001')->value('id');
        DB::table('meal_sessions')->updateOrInsert(
            ['meeting_event_id' => $eventId, 'entitlement_type' => 'COFFEE_BREAK', 'session_number' => 1],
            [
                'hotel_id' => $hotelId,
                'name' => 'Coffee Break 1',
                'starts_at' => '2026-07-15 10:00:00+07',
                'ends_at' => '2026-07-15 10:30:00+07',
                'status' => 'OPEN',
                'location' => 'Meeting foyer',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
        DB::table('participant_entitlements')->updateOrInsert(
            ['participant_id' => $participantId, 'meeting_event_id' => $eventId, 'entitlement_type' => 'COFFEE_BREAK'],
            [
                'total_quantity' => 1,
                'redeemed_quantity' => 0,
                'remaining_quantity' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        if (! DB::table('participant_qr_credentials')->where('participant_id', $participantId)->where('status', 'ACTIVE')->exists()) {
            $seedToken = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
            DB::table('participant_qr_credentials')->insert([
                'participant_id' => $participantId,
                'token_hash' => hash('sha256', $seedToken),
                'token_last_four' => substr($seedToken, -4),
                'status' => 'ACTIVE',
                'issued_at' => $now,
                'expires_at' => Carbon::parse('2026-07-16 11:00:00+07'),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function hotelShortName(string $hotelCode): string
    {
        return match ($hotelCode) {
            'ORIA' => 'Oria',
            'ASHLEY-WH' => 'Ashley',
            'AONE-WH' => 'AONE',
            'MORRISSEY' => 'Morrissey',
            default => $hotelCode,
        };
    }
}
