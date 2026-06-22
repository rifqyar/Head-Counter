<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $legacyTables = [
        'trx_meeting_attendance',
        'trx_meeting_schedule',
        'm_meeting_rooms',
        'm_client',
        'm_packages',
        'r_room_status',
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $this->copyLegacyDataToCanonicalTables();
        $this->remapLegacyQrDetails();
        $this->dropLegacyForeignKeys();
        $this->dropLegacyTables();
        $this->createCompatibilityFunctions();
        $this->createCompatibilityViews();
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $this->dropCompatibilityViews();
        $this->recreateLegacyTablesFromCanonicalData();
        $this->dropCompatibilityFunctions();
    }

    private function copyLegacyDataToCanonicalTables(): void
    {
        $hotelId = $this->defaultHotelId();
        $now = now();

        if (! $hotelId) {
            return;
        }

        if ($this->baseTableExists('m_meeting_rooms')) {
            $hasCapacity = Schema::hasColumn('m_meeting_rooms', 'capacity');
            foreach (DB::table('m_meeting_rooms')->orderBy('id')->get() as $room) {
                DB::table('meeting_rooms')->updateOrInsert(
                    ['hotel_id' => $hotelId, 'code' => $room->kd_room],
                    [
                        'name' => $room->name,
                        'capacity' => $hasCapacity ? max(0, (int) ($room->capacity ?? 0)) : 0,
                        'operational_status' => $this->roomStatus($room->room_availability),
                        'facilities' => json_encode(['legacy_room_code' => $room->kd_room]),
                        'created_at' => $room->created_at ?? $now,
                        'updated_at' => $room->updated_at ?? $now,
                    ]
                );
            }
        }

        if ($this->baseTableExists('m_client')) {
            foreach (DB::table('m_client')->orderBy('id')->get() as $client) {
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

                $clientId = DB::table('clients')->where('hotel_id', $hotelId)->where('external_id', $client->code)->value('id');
                if ($clientId && Schema::hasTable('client_hotel')) {
                    DB::table('client_hotel')->updateOrInsert(
                        ['client_id' => $clientId, 'hotel_id' => $hotelId],
                        [
                            'hotel_specific_code' => $client->code,
                            'status' => 'ACTIVE',
                            'metadata' => json_encode(['legacy_view' => true]),
                            'created_at' => $client->created_at ?? $now,
                            'updated_at' => $client->updated_at ?? $now,
                        ]
                    );
                }
            }
        }

        if ($this->baseTableExists('m_packages')) {
            foreach (DB::table('m_packages')->orderBy('id')->get() as $package) {
                DB::table('meeting_packages')->updateOrInsert(
                    ['hotel_id' => $hotelId, 'code' => $package->kd_pck],
                    [
                        'name' => $package->name,
                        'description' => $package->details,
                        'price' => $package->price ?? 0,
                        'is_active' => true,
                        'metadata' => json_encode(['legacy_count_qr' => (int) $package->count_qr]),
                        'created_at' => $package->created_at ?? $now,
                        'updated_at' => $package->updated_at ?? $now,
                    ]
                );

                $packageId = DB::table('meeting_packages')->where('hotel_id', $hotelId)->where('code', $package->kd_pck)->value('id');
                if ($packageId) {
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
            }
        }

        if ($this->baseTableExists('trx_meeting_schedule')) {
            foreach (DB::table('trx_meeting_schedule')->orderBy('id')->get() as $schedule) {
                $this->copyLegacySchedule($schedule, $hotelId, $now);
            }
        }

        if ($this->baseTableExists('trx_meeting_attendance')) {
            foreach (DB::table('trx_meeting_attendance')->orderBy('id')->get() as $attendance) {
                $this->copyLegacyAttendance($attendance, $hotelId, $now);
            }
        }
    }

    private function copyLegacySchedule(object $schedule, int $hotelId, Carbon $now): void
    {
        $clientId = DB::table('clients')->where('hotel_id', $hotelId)->where('external_id', $schedule->code_client)->value('id');
        $roomId = DB::table('meeting_rooms')->where('hotel_id', $hotelId)->where('code', $schedule->room)->value('id');
        $packageId = DB::table('meeting_packages')->where('hotel_id', $hotelId)->where('code', $schedule->package)->value('id');
        $eventDate = $schedule->tgl_start ?? $schedule->tgl_meeting ?? $now->toDateString();
        $endDate = $schedule->tgl_end ?? $eventDate;
        $startAt = Carbon::parse($eventDate.' '.$schedule->jam_mulai);
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
                'meeting_qr_path' => $schedule->qr_path ?? null,
                'created_at' => $schedule->created_at ?? $now,
                'updated_at' => $schedule->updated_at ?? $now,
            ]
        );

        if (! $packageId) {
            return;
        }

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

    private function copyLegacyAttendance(object $attendance, int $hotelId, Carbon $now): void
    {
        $event = DB::table('meeting_events')->where('hotel_id', $hotelId)->where('legacy_trx_number', $attendance->trx_metting_number)->first();
        if (! $event) {
            return;
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
                'metadata' => json_encode([
                    'legacy_attendance_id' => $attendance->id,
                    'legacy_fingerprint' => $attendance->mac_address ?? null,
                    'legacy_jabatan' => $attendance->jabatan ?? null,
                    'legacy_qr_path' => $attendance->qr_path ?? null,
                ]),
                'created_at' => $attendance->created_at ?? $now,
                'updated_at' => $attendance->updated_at ?? $now,
            ]
        );

        if ((int) $attendance->scanned_qr <= 0) {
            return;
        }

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

    private function remapLegacyQrDetails(): void
    {
        if (! $this->baseTableExists('trx_meeting_schedule') || ! Schema::hasTable('qr_detail')) {
            return;
        }

        DB::statement(<<<'SQL'
            UPDATE qr_detail q
            SET meeting_id = e.id
            FROM trx_meeting_schedule s
            JOIN meeting_events e ON e.legacy_trx_number = s.trx_number
            WHERE q.meeting_id = s.id
        SQL);
    }

    private function dropLegacyForeignKeys(): void
    {
        foreach ([
            'm_meeting_rooms_room_availability_foreign' => 'm_meeting_rooms',
            'qr_detail_meeting_id_foreign' => 'qr_detail',
            'trx_meeting_attendance_trx_metting_number_foreign' => 'trx_meeting_attendance',
            'trx_meeting_schedule_room_foreign' => 'trx_meeting_schedule',
            'trx_meeting_schedule_package_foreign' => 'trx_meeting_schedule',
            'trx_meeting_schedule_code_client_foreign' => 'trx_meeting_schedule',
        ] as $constraint => $table) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$constraint}");
            }
        }
    }

    private function dropLegacyTables(): void
    {
        foreach ($this->legacyTables as $table) {
            if ($this->baseTableExists($table)) {
                DB::statement("DROP TABLE {$table} CASCADE");
            }
        }
    }

    private function createCompatibilityFunctions(): void
    {
        DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION hc_legacy_default_hotel_id()
            RETURNS bigint
            LANGUAGE sql
            STABLE
            AS $$
                SELECT COALESCE(
                    (SELECT id FROM hotels WHERE code = 'ORIA' LIMIT 1),
                    (SELECT id FROM hotels ORDER BY id LIMIT 1)
                )
            $$;
        SQL);

        DB::unprepared($this->compatibilityTriggerSql());
    }

    private function createCompatibilityViews(): void
    {
        DB::statement(<<<'SQL'
            CREATE VIEW r_room_status AS
            SELECT *
            FROM (VALUES
                (1::bigint, 'AVAILABLE'::varchar, 'Available'::varchar, 'Ruangan Tersedia'::varchar, now(), now()),
                (2::bigint, 'RESERVED'::varchar, 'Reserved'::varchar, 'Ruangan sudah dibooking untuk acara lain'::varchar, now(), now()),
                (3::bigint, 'OCCUPIED'::varchar, 'Occupied'::varchar, 'Ruangan sedang digunakan untuk acara lain'::varchar, now(), now()),
                (4::bigint, 'CLEANING'::varchar, 'Cleaning'::varchar, 'Ruangan sedang dibersihkan'::varchar, now(), now()),
                (5::bigint, 'MAINTENANCE'::varchar, 'Maintenance'::varchar, 'Ruangan sedang dalam perawatan'::varchar, now(), now()),
                (6::bigint, 'INACTIVE'::varchar, 'Inactive'::varchar, 'Ruangan tidak aktif'::varchar, now(), now())
            ) AS statuses(id, kd_status, name, description, created_at, updated_at);
        SQL);
        DB::statement('CREATE TRIGGER r_room_status_legacy_write INSTEAD OF INSERT OR UPDATE OR DELETE ON r_room_status FOR EACH ROW EXECUTE FUNCTION hc_legacy_room_status_write()');

        DB::statement(<<<'SQL'
            CREATE VIEW m_meeting_rooms AS
            SELECT id, code AS kd_room, name, operational_status AS room_availability, capacity, created_at, updated_at
            FROM meeting_rooms
            WHERE hotel_id = hc_legacy_default_hotel_id();
        SQL);
        DB::statement('CREATE TRIGGER m_meeting_rooms_legacy_write INSTEAD OF INSERT OR UPDATE OR DELETE ON m_meeting_rooms FOR EACH ROW EXECUTE FUNCTION hc_legacy_room_write()');

        DB::statement(<<<'SQL'
            CREATE VIEW m_client AS
            SELECT
                c.id,
                COALESCE(ch.hotel_specific_code, c.external_id) AS code,
                c.company_name AS name,
                c.contact_name AS contact_person,
                c.contact_phone AS company_phone,
                c.contact_email AS email,
                false AS deleted_status,
                c.created_at,
                c.updated_at
            FROM clients c
            LEFT JOIN client_hotel ch ON ch.client_id = c.id AND ch.hotel_id = c.hotel_id
            WHERE c.hotel_id = hc_legacy_default_hotel_id();
        SQL);
        DB::statement('CREATE TRIGGER m_client_legacy_write INSTEAD OF INSERT OR UPDATE OR DELETE ON m_client FOR EACH ROW EXECUTE FUNCTION hc_legacy_client_write()');

        DB::statement(<<<'SQL'
            CREATE VIEW m_packages AS
            SELECT
                p.id,
                p.code AS kd_pck,
                p.name,
                p.price,
                p.description AS details,
                COALESCE(NULLIF(p.metadata->>'legacy_count_qr', '')::integer, e.quantity, 1) AS count_qr,
                p.created_at,
                p.updated_at
            FROM meeting_packages p
            LEFT JOIN LATERAL (
                SELECT quantity
                FROM package_entitlements pe
                WHERE pe.package_id = p.id
                ORDER BY pe.id
                LIMIT 1
            ) e ON true
            WHERE p.hotel_id = hc_legacy_default_hotel_id();
        SQL);
        DB::statement('CREATE TRIGGER m_packages_legacy_write INSTEAD OF INSERT OR UPDATE OR DELETE ON m_packages FOR EACH ROW EXECUTE FUNCTION hc_legacy_package_write()');

        DB::statement(<<<'SQL'
            CREATE VIEW trx_meeting_schedule AS
            SELECT
                e.id,
                COALESCE(e.legacy_trx_number, b.booking_number) AS trx_number,
                COALESCE(ch.hotel_specific_code, c.external_id) AS code_client,
                e.event_date AS tgl_start,
                e.end_at::date AS tgl_end,
                e.start_at::time AS jam_mulai,
                e.end_at::time AS jam_selesai,
                e.expected_participants AS kuota,
                e.meeting_qr_path AS qr_path,
                p.code AS package,
                r.code AS room,
                e.created_at,
                e.updated_at
            FROM meeting_events e
            LEFT JOIN bookings b ON b.id = e.booking_id
            LEFT JOIN clients c ON c.id = b.client_id
            LEFT JOIN client_hotel ch ON ch.client_id = c.id AND ch.hotel_id = e.hotel_id
            LEFT JOIN meeting_rooms r ON r.id = e.meeting_room_id
            LEFT JOIN LATERAL (
                SELECT mp.code
                FROM meeting_package_assignments mpa
                JOIN meeting_packages mp ON mp.id = mpa.package_id
                WHERE mpa.meeting_event_id = e.id
                ORDER BY mpa.id
                LIMIT 1
            ) p ON true
            WHERE e.hotel_id = hc_legacy_default_hotel_id();
        SQL);
        DB::statement('CREATE TRIGGER trx_meeting_schedule_legacy_write INSTEAD OF INSERT OR UPDATE OR DELETE ON trx_meeting_schedule FOR EACH ROW EXECUTE FUNCTION hc_legacy_schedule_write()');

        DB::statement(<<<'SQL'
            CREATE VIEW trx_meeting_attendance AS
            SELECT
                p.id,
                COALESCE(e.legacy_trx_number, b.booking_number) AS trx_metting_number,
                p.full_name AS name,
                p.phone AS phone_number,
                p.metadata->>'legacy_jabatan' AS jabatan,
                p.company_name AS company,
                p.metadata->>'legacy_fingerprint' AS mac_address,
                p.metadata->>'legacy_qr_path' AS qr_path,
                CASE WHEN p.status = 'CHECKED_IN' OR ma.id IS NOT NULL THEN 1 ELSE 0 END AS scanned_qr,
                p.created_at,
                p.updated_at
            FROM participants p
            JOIN meeting_events e ON e.id = p.meeting_event_id
            LEFT JOIN bookings b ON b.id = e.booking_id
            LEFT JOIN meeting_attendances ma ON ma.participant_id = p.id AND ma.attendance_type = 'MEETING_CHECKIN'
            WHERE p.hotel_id = hc_legacy_default_hotel_id();
        SQL);
        DB::statement('CREATE TRIGGER trx_meeting_attendance_legacy_write INSTEAD OF INSERT OR UPDATE OR DELETE ON trx_meeting_attendance FOR EACH ROW EXECUTE FUNCTION hc_legacy_attendance_write()');
    }

    private function compatibilityTriggerSql(): string
    {
        return <<<'SQL'
CREATE OR REPLACE FUNCTION hc_legacy_room_status_write()
RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
    IF TG_OP = 'DELETE' THEN
        RETURN OLD;
    END IF;

    RETURN NEW;
END;
$$;

CREATE OR REPLACE FUNCTION hc_legacy_room_write()
RETURNS trigger
LANGUAGE plpgsql
AS $$
DECLARE
    v_hotel_id bigint := hc_legacy_default_hotel_id();
    v_id bigint;
BEGIN
    IF TG_OP = 'DELETE' THEN
        DELETE FROM meeting_rooms WHERE id = OLD.id;
        RETURN OLD;
    END IF;

    IF TG_OP = 'INSERT' THEN
        INSERT INTO meeting_rooms (hotel_id, code, name, capacity, operational_status, facilities, created_at, updated_at)
        VALUES (
            v_hotel_id,
            NEW.kd_room,
            NEW.name,
            COALESCE(NEW.capacity, 0),
            COALESCE(NEW.room_availability, 'AVAILABLE'),
            jsonb_build_object('legacy_room_code', NEW.kd_room),
            COALESCE(NEW.created_at, now()),
            COALESCE(NEW.updated_at, now())
        )
        ON CONFLICT (hotel_id, code) DO UPDATE SET
            name = EXCLUDED.name,
            capacity = EXCLUDED.capacity,
            operational_status = EXCLUDED.operational_status,
            updated_at = EXCLUDED.updated_at
        RETURNING id INTO v_id;

        NEW.id := v_id;
        RETURN NEW;
    END IF;

    UPDATE meeting_rooms
    SET code = NEW.kd_room,
        name = NEW.name,
        capacity = COALESCE(NEW.capacity, capacity),
        operational_status = COALESCE(NEW.room_availability, operational_status),
        updated_at = COALESCE(NEW.updated_at, now())
    WHERE id = OLD.id
    RETURNING id INTO v_id;

    NEW.id := COALESCE(v_id, OLD.id);
    RETURN NEW;
END;
$$;

CREATE OR REPLACE FUNCTION hc_legacy_client_write()
RETURNS trigger
LANGUAGE plpgsql
AS $$
DECLARE
    v_hotel_id bigint := hc_legacy_default_hotel_id();
    v_id bigint;
BEGIN
    IF TG_OP = 'DELETE' THEN
        DELETE FROM clients WHERE id = OLD.id;
        RETURN OLD;
    END IF;

    IF TG_OP = 'INSERT' THEN
        INSERT INTO clients (hotel_id, external_id, company_name, contact_name, contact_phone, contact_email, metadata, created_at, updated_at)
        VALUES (
            v_hotel_id,
            NEW.code,
            NEW.name,
            NEW.contact_person,
            NEW.company_phone,
            NEW.email,
            jsonb_build_object('legacy_client_code', NEW.code),
            COALESCE(NEW.created_at, now()),
            COALESCE(NEW.updated_at, now())
        )
        ON CONFLICT (hotel_id, external_id) DO UPDATE SET
            company_name = EXCLUDED.company_name,
            contact_name = EXCLUDED.contact_name,
            contact_phone = EXCLUDED.contact_phone,
            contact_email = EXCLUDED.contact_email,
            updated_at = EXCLUDED.updated_at
        RETURNING id INTO v_id;

        INSERT INTO client_hotel (client_id, hotel_id, hotel_specific_code, status, metadata, created_at, updated_at)
        VALUES (v_id, v_hotel_id, NEW.code, 'ACTIVE', jsonb_build_object('legacy_view', true), now(), now())
        ON CONFLICT (client_id, hotel_id) DO UPDATE SET
            hotel_specific_code = EXCLUDED.hotel_specific_code,
            status = 'ACTIVE',
            updated_at = now();

        NEW.id := v_id;
        RETURN NEW;
    END IF;

    UPDATE clients
    SET external_id = NEW.code,
        company_name = NEW.name,
        contact_name = NEW.contact_person,
        contact_phone = NEW.company_phone,
        contact_email = NEW.email,
        updated_at = COALESCE(NEW.updated_at, now())
    WHERE id = OLD.id
    RETURNING id INTO v_id;

    INSERT INTO client_hotel (client_id, hotel_id, hotel_specific_code, status, metadata, created_at, updated_at)
    VALUES (COALESCE(v_id, OLD.id), v_hotel_id, NEW.code, 'ACTIVE', jsonb_build_object('legacy_view', true), now(), now())
    ON CONFLICT (client_id, hotel_id) DO UPDATE SET
        hotel_specific_code = EXCLUDED.hotel_specific_code,
        status = 'ACTIVE',
        updated_at = now();

    NEW.id := COALESCE(v_id, OLD.id);
    RETURN NEW;
END;
$$;

CREATE OR REPLACE FUNCTION hc_legacy_package_write()
RETURNS trigger
LANGUAGE plpgsql
AS $$
DECLARE
    v_hotel_id bigint := hc_legacy_default_hotel_id();
    v_id bigint;
BEGIN
    IF TG_OP = 'DELETE' THEN
        DELETE FROM meeting_packages WHERE id = OLD.id;
        RETURN OLD;
    END IF;

    IF TG_OP = 'INSERT' THEN
        INSERT INTO meeting_packages (hotel_id, code, name, description, price, is_active, metadata, created_at, updated_at)
        VALUES (
            v_hotel_id,
            NEW.kd_pck,
            NEW.name,
            NEW.details,
            COALESCE(NEW.price, 0),
            true,
            jsonb_build_object('legacy_count_qr', COALESCE(NEW.count_qr, 1)),
            COALESCE(NEW.created_at, now()),
            COALESCE(NEW.updated_at, now())
        )
        ON CONFLICT (hotel_id, code) DO UPDATE SET
            name = EXCLUDED.name,
            description = EXCLUDED.description,
            price = EXCLUDED.price,
            metadata = COALESCE(meeting_packages.metadata, '{}'::jsonb) || jsonb_build_object('legacy_count_qr', COALESCE(NEW.count_qr, 1)),
            updated_at = EXCLUDED.updated_at
        RETURNING id INTO v_id;

        UPDATE package_entitlements
        SET quantity = GREATEST(1, COALESCE(NEW.count_qr, 1)),
            metadata = COALESCE(metadata, '{}'::jsonb) || jsonb_build_object('legacy_field', 'count_qr'),
            updated_at = now()
        WHERE package_id = v_id AND entitlement_type = 'CUSTOM';

        IF NOT FOUND THEN
            INSERT INTO package_entitlements (package_id, entitlement_type, quantity, metadata, created_at, updated_at)
            VALUES (v_id, 'CUSTOM', GREATEST(1, COALESCE(NEW.count_qr, 1)), jsonb_build_object('legacy_field', 'count_qr'), now(), now());
        END IF;

        NEW.id := v_id;
        RETURN NEW;
    END IF;

    UPDATE meeting_packages
    SET code = NEW.kd_pck,
        name = NEW.name,
        description = NEW.details,
        price = COALESCE(NEW.price, price),
        metadata = COALESCE(metadata, '{}'::jsonb) || jsonb_build_object('legacy_count_qr', COALESCE(NEW.count_qr, 1)),
        updated_at = COALESCE(NEW.updated_at, now())
    WHERE id = OLD.id
    RETURNING id INTO v_id;

    UPDATE package_entitlements
    SET quantity = GREATEST(1, COALESCE(NEW.count_qr, 1)),
        metadata = COALESCE(metadata, '{}'::jsonb) || jsonb_build_object('legacy_field', 'count_qr'),
        updated_at = now()
    WHERE package_id = COALESCE(v_id, OLD.id) AND entitlement_type = 'CUSTOM';

    IF NOT FOUND THEN
        INSERT INTO package_entitlements (package_id, entitlement_type, quantity, metadata, created_at, updated_at)
        VALUES (COALESCE(v_id, OLD.id), 'CUSTOM', GREATEST(1, COALESCE(NEW.count_qr, 1)), jsonb_build_object('legacy_field', 'count_qr'), now(), now());
    END IF;

    NEW.id := COALESCE(v_id, OLD.id);
    RETURN NEW;
END;
$$;

CREATE OR REPLACE FUNCTION hc_legacy_schedule_write()
RETURNS trigger
LANGUAGE plpgsql
AS $$
DECLARE
    v_hotel_id bigint := hc_legacy_default_hotel_id();
    v_client_id bigint;
    v_booking_id bigint;
    v_room_id bigint;
    v_package_id bigint;
    v_event_id bigint;
    v_start_at timestamptz;
    v_end_at timestamptz;
BEGIN
    IF TG_OP = 'DELETE' THEN
        DELETE FROM meeting_events WHERE id = OLD.id;
        RETURN OLD;
    END IF;

    SELECT id INTO v_client_id FROM clients WHERE hotel_id = v_hotel_id AND external_id = NEW.code_client LIMIT 1;
    SELECT id INTO v_room_id FROM meeting_rooms WHERE hotel_id = v_hotel_id AND code = NEW.room LIMIT 1;
    SELECT id INTO v_package_id FROM meeting_packages WHERE hotel_id = v_hotel_id AND code = NEW.package LIMIT 1;

    v_start_at := ((COALESCE(NEW.tgl_start, current_date))::date + COALESCE(NEW.jam_mulai, '00:00'::time))::timestamptz;
    v_end_at := ((COALESCE(NEW.tgl_end, NEW.tgl_start, current_date))::date + COALESCE(NEW.jam_selesai, '23:59'::time))::timestamptz;

    INSERT INTO bookings (hotel_id, client_id, booking_number, booking_source, booking_date, status, notes, created_at, updated_at)
    VALUES (v_hotel_id, v_client_id, NEW.trx_number, 'LEGACY', NEW.tgl_start, 'CONFIRMED', 'Generated from legacy compatibility view.', COALESCE(NEW.created_at, now()), COALESCE(NEW.updated_at, now()))
    ON CONFLICT (hotel_id, booking_number) DO UPDATE SET
        client_id = EXCLUDED.client_id,
        booking_date = EXCLUDED.booking_date,
        updated_at = EXCLUDED.updated_at
    RETURNING id INTO v_booking_id;

    IF TG_OP = 'INSERT' THEN
        INSERT INTO meeting_events (hotel_id, booking_id, meeting_room_id, event_name, event_date, start_at, end_at, expected_participants, actual_participants, status, legacy_trx_number, meeting_qr_path, created_at, updated_at)
        VALUES (v_hotel_id, v_booking_id, v_room_id, 'Meeting ' || NEW.trx_number, NEW.tgl_start, v_start_at, v_end_at, COALESCE(NEW.kuota, 0), 0, 'SCHEDULED', NEW.trx_number, NEW.qr_path, COALESCE(NEW.created_at, now()), COALESCE(NEW.updated_at, now()))
        ON CONFLICT (hotel_id, legacy_trx_number) DO UPDATE SET
            booking_id = EXCLUDED.booking_id,
            meeting_room_id = EXCLUDED.meeting_room_id,
            event_date = EXCLUDED.event_date,
            start_at = EXCLUDED.start_at,
            end_at = EXCLUDED.end_at,
            expected_participants = EXCLUDED.expected_participants,
            meeting_qr_path = EXCLUDED.meeting_qr_path,
            updated_at = EXCLUDED.updated_at
        RETURNING id INTO v_event_id;
    ELSE
        UPDATE meeting_events
        SET booking_id = v_booking_id,
            meeting_room_id = v_room_id,
            event_date = NEW.tgl_start,
            start_at = v_start_at,
            end_at = v_end_at,
            expected_participants = COALESCE(NEW.kuota, expected_participants),
            legacy_trx_number = NEW.trx_number,
            meeting_qr_path = NEW.qr_path,
            updated_at = COALESCE(NEW.updated_at, now())
        WHERE id = OLD.id
        RETURNING id INTO v_event_id;
    END IF;

    IF v_package_id IS NOT NULL THEN
        INSERT INTO meeting_package_assignments (meeting_event_id, package_id, participant_quota, unit_price, notes, created_at, updated_at)
        SELECT v_event_id, v_package_id, COALESCE(NEW.kuota, 0), price, 'Synced from legacy compatibility view.', now(), now()
        FROM meeting_packages
        WHERE id = v_package_id
        ON CONFLICT (meeting_event_id, package_id) DO UPDATE SET
            participant_quota = EXCLUDED.participant_quota,
            unit_price = EXCLUDED.unit_price,
            updated_at = now();
    END IF;

    NEW.id := v_event_id;
    RETURN NEW;
END;
$$;

CREATE OR REPLACE FUNCTION hc_legacy_attendance_write()
RETURNS trigger
LANGUAGE plpgsql
AS $$
DECLARE
    v_hotel_id bigint := hc_legacy_default_hotel_id();
    v_event_id bigint;
    v_participant_id bigint;
    v_participant_number varchar;
BEGIN
    IF TG_OP = 'DELETE' THEN
        DELETE FROM participants WHERE id = OLD.id;
        RETURN OLD;
    END IF;

    SELECT id INTO v_event_id
    FROM meeting_events
    WHERE hotel_id = v_hotel_id
      AND legacy_trx_number = NEW.trx_metting_number
    LIMIT 1;

    IF v_event_id IS NULL THEN
        RAISE EXCEPTION 'Legacy meeting % was not found in canonical meeting_events.', NEW.trx_metting_number;
    END IF;

    IF TG_OP = 'INSERT' THEN
        v_participant_id := nextval(pg_get_serial_sequence('participants', 'id'));
        v_participant_number := NEW.trx_metting_number || '-LEGACY-' || v_participant_id;

        INSERT INTO participants (id, hotel_id, meeting_event_id, participant_number, full_name, company_name, phone, normalized_phone, registration_source, status, registered_at, checked_in_at, metadata, created_at, updated_at)
        VALUES (
            v_participant_id,
            v_hotel_id,
            v_event_id,
            v_participant_number,
            NEW.name,
            NEW.company,
            NEW.phone_number,
            regexp_replace(COALESCE(NEW.phone_number, ''), '[^0-9+]', '', 'g'),
            'LEGACY',
            CASE WHEN COALESCE(NEW.scanned_qr, 0) > 0 THEN 'CHECKED_IN' ELSE 'REGISTERED' END,
            COALESCE(NEW.created_at, now()),
            CASE WHEN COALESCE(NEW.scanned_qr, 0) > 0 THEN COALESCE(NEW.updated_at, now()) ELSE NULL END,
            jsonb_build_object('legacy_jabatan', NEW.jabatan, 'legacy_fingerprint', NEW.mac_address, 'legacy_qr_path', NEW.qr_path),
            COALESCE(NEW.created_at, now()),
            COALESCE(NEW.updated_at, now())
        );
    ELSE
        v_participant_id := OLD.id;
        UPDATE participants
        SET full_name = NEW.name,
            company_name = NEW.company,
            phone = NEW.phone_number,
            normalized_phone = regexp_replace(COALESCE(NEW.phone_number, ''), '[^0-9+]', '', 'g'),
            status = CASE WHEN COALESCE(NEW.scanned_qr, 0) > 0 THEN 'CHECKED_IN' ELSE status END,
            checked_in_at = CASE WHEN COALESCE(NEW.scanned_qr, 0) > 0 THEN COALESCE(NEW.updated_at, now()) ELSE checked_in_at END,
            metadata = COALESCE(metadata, '{}'::jsonb) || jsonb_build_object('legacy_jabatan', NEW.jabatan, 'legacy_fingerprint', NEW.mac_address, 'legacy_qr_path', NEW.qr_path),
            updated_at = COALESCE(NEW.updated_at, now())
        WHERE id = OLD.id;
    END IF;

    IF COALESCE(NEW.scanned_qr, 0) > 0 THEN
        INSERT INTO meeting_attendances (meeting_event_id, participant_id, attendance_type, attended_at, verification_method, metadata, created_at)
        VALUES (v_event_id, v_participant_id, 'MEETING_CHECKIN', COALESCE(NEW.updated_at, now()), 'LEGACY_QR', jsonb_build_object('legacy_view', true), now())
        ON CONFLICT (participant_id) WHERE attendance_type = 'MEETING_CHECKIN' DO NOTHING;
    END IF;

    NEW.id := v_participant_id;
    RETURN NEW;
END;
$$;
SQL;
    }

    private function dropCompatibilityViews(): void
    {
        foreach ($this->legacyTables as $table) {
            DB::statement("DROP VIEW IF EXISTS {$table} CASCADE");
        }
    }

    private function recreateLegacyTablesFromCanonicalData(): void
    {
        DB::statement(<<<'SQL'
            CREATE TABLE r_room_status AS
            SELECT *
            FROM (VALUES
                (1::bigint, 'AVAILABLE'::varchar, 'Available'::varchar, 'Ruangan Tersedia'::varchar, now(), now()),
                (2::bigint, 'RESERVED'::varchar, 'Reserved'::varchar, 'Ruangan sudah dibooking untuk acara lain'::varchar, now(), now()),
                (3::bigint, 'OCCUPIED'::varchar, 'Occupied'::varchar, 'Ruangan sedang digunakan untuk acara lain'::varchar, now(), now())
            ) AS statuses(id, kd_status, name, description, created_at, updated_at)
        SQL);
        DB::statement('CREATE TABLE m_meeting_rooms AS SELECT id, code AS kd_room, name, operational_status AS room_availability, capacity, created_at, updated_at FROM meeting_rooms WHERE hotel_id = hc_legacy_default_hotel_id()');
        DB::statement('CREATE TABLE m_client AS SELECT c.id, COALESCE(ch.hotel_specific_code, c.external_id) AS code, c.company_name AS name, c.contact_name AS contact_person, c.contact_phone AS company_phone, c.contact_email AS email, false AS deleted_status, c.created_at, c.updated_at FROM clients c LEFT JOIN client_hotel ch ON ch.client_id = c.id AND ch.hotel_id = c.hotel_id WHERE c.hotel_id = hc_legacy_default_hotel_id()');
        DB::statement("CREATE TABLE m_packages AS SELECT p.id, p.code AS kd_pck, p.name, p.price, p.description AS details, COALESCE(NULLIF(p.metadata->>'legacy_count_qr', '')::integer, 1) AS count_qr, p.created_at, p.updated_at FROM meeting_packages p WHERE p.hotel_id = hc_legacy_default_hotel_id()");
        DB::statement('CREATE TABLE trx_meeting_schedule AS SELECT e.id, COALESCE(e.legacy_trx_number, b.booking_number) AS trx_number, c.external_id AS code_client, e.event_date AS tgl_start, e.end_at::date AS tgl_end, e.start_at::time AS jam_mulai, e.end_at::time AS jam_selesai, e.expected_participants AS kuota, e.meeting_qr_path AS qr_path, mp.code AS package, r.code AS room, e.created_at, e.updated_at FROM meeting_events e LEFT JOIN bookings b ON b.id = e.booking_id LEFT JOIN clients c ON c.id = b.client_id LEFT JOIN meeting_rooms r ON r.id = e.meeting_room_id LEFT JOIN meeting_package_assignments mpa ON mpa.meeting_event_id = e.id LEFT JOIN meeting_packages mp ON mp.id = mpa.package_id WHERE e.hotel_id = hc_legacy_default_hotel_id()');
        DB::statement("CREATE TABLE trx_meeting_attendance AS SELECT p.id, COALESCE(e.legacy_trx_number, b.booking_number) AS trx_metting_number, p.full_name AS name, p.phone AS phone_number, p.metadata->>'legacy_jabatan' AS jabatan, p.company_name AS company, p.metadata->>'legacy_fingerprint' AS mac_address, p.metadata->>'legacy_qr_path' AS qr_path, CASE WHEN p.status = 'CHECKED_IN' THEN 1 ELSE 0 END AS scanned_qr, p.created_at, p.updated_at FROM participants p JOIN meeting_events e ON e.id = p.meeting_event_id LEFT JOIN bookings b ON b.id = e.booking_id WHERE p.hotel_id = hc_legacy_default_hotel_id()");
    }

    private function dropCompatibilityFunctions(): void
    {
        foreach ([
            'hc_legacy_attendance_write',
            'hc_legacy_schedule_write',
            'hc_legacy_package_write',
            'hc_legacy_client_write',
            'hc_legacy_room_write',
            'hc_legacy_room_status_write',
            'hc_legacy_default_hotel_id',
        ] as $function) {
            DB::statement("DROP FUNCTION IF EXISTS {$function} CASCADE");
        }
    }

    private function defaultHotelId(): ?int
    {
        return DB::table('hotels')->where('code', 'ORIA')->value('id')
            ?? DB::table('hotels')->orderBy('id')->value('id');
    }

    private function baseTableExists(string $table): bool
    {
        return DB::table('pg_class')
            ->join('pg_namespace', 'pg_namespace.oid', '=', 'pg_class.relnamespace')
            ->where('pg_namespace.nspname', 'public')
            ->where('pg_class.relname', $table)
            ->where('pg_class.relkind', 'r')
            ->exists();
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
};
