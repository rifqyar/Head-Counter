<?php

namespace Tests\Feature;

use App\Domain\Attendance\MeetingAttendance;
use App\Domain\Booking\Booking;
use App\Domain\Booking\Client;
use App\Domain\Catering\MeetingPackage;
use App\Domain\Catering\PackageEntitlement;
use App\Domain\Hotel\Hotel;
use App\Domain\Meeting\MeetingEvent;
use App\Domain\Meeting\MeetingRoom;
use App\Domain\Participant\Participant;
use App\Enums\AttendanceType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PhaseThreeCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_canonical_crud_ui_paths_for_core_resources(): void
    {
        $this->seed();
        [$hotel, $user] = $this->hotelUser();

        $this->actingAs($user)->get('/meeting-rooms/create')->assertRedirect('/redirect')->assertSessionHas('Redirect', 'meeting-rooms/create');
        $this->ajaxGet($user, '/meeting-rooms/create')->assertOk()->assertSee('Create Meeting Room');
        $this->actingAs($user)->post('/meeting-rooms', [
            'code' => 'QA-ROOM',
            'name' => 'QA Room',
            'floor' => '3',
            'capacity' => 30,
            'operational_status' => 'AVAILABLE',
        ])->assertRedirect();
        $room = MeetingRoom::where('code', 'QA-ROOM')->firstOrFail();
        $this->ajaxGet($user, '/meeting-rooms/'.$room->id)->assertOk()->assertSee('QA Room');
        $this->actingAs($user)->put('/meeting-rooms/'.$room->id, [
            'code' => 'QA-ROOM',
            'name' => 'QA Room Updated',
            'floor' => '3',
            'capacity' => 32,
            'operational_status' => 'AVAILABLE',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->actingAs($user)->delete('/meeting-rooms/'.$room->id)->assertRedirect();
        $this->assertSame('INACTIVE', $room->refresh()->operational_status->value);

        $this->actingAs($user)->post('/clients', [
            'external_id' => 'QA-CLIENT',
            'company_name' => 'QA Client Ltd',
            'contact_name' => 'QA Contact',
            'contact_email' => 'qa-client@example.test',
            'contact_phone' => '+628121111111',
        ])->assertRedirect();
        $client = Client::where('external_id', 'QA-CLIENT')->firstOrFail();

        $this->actingAs($user)->post('/bookings', [
            'client_id' => $client->id,
            'booking_number' => 'QA-BKG-001',
            'booking_source' => 'DIRECT',
            'booking_date' => '2026-08-01',
            'status' => 'CONFIRMED',
        ])->assertRedirect();
        $booking = Booking::where('booking_number', 'QA-BKG-001')->firstOrFail();
        $this->ajaxGet($user, '/bookings/create')
            ->assertOk()
            ->assertSee('booking-wizard', false)
            ->assertSee('initBookingWizard', false)
            ->assertSee('Save Booking');
        $this->actingAs($user)->put('/bookings/'.$booking->id, [
            'client_id' => $client->id,
            'booking_number' => 'QA-BKG-001',
            'booking_source' => 'DIRECT',
            'booking_date' => '2026-08-02',
            'status' => 'CONFIRMED',
        ])->assertRedirect();

        $activeRoom = MeetingRoom::where('hotel_id', $hotel->id)->where('operational_status', 'AVAILABLE')->firstOrFail();
        $this->actingAs($user)->post('/meetings', [
            'booking_id' => $booking->id,
            'meeting_room_id' => $activeRoom->id,
            'event_name' => 'QA Meeting',
            'event_date' => '2026-08-03',
            'start_at' => '2026-08-03 09:00:00',
            'end_at' => '2026-08-03 10:00:00',
            'expected_participants' => 12,
            'actual_participants' => 0,
            'status' => 'SCHEDULED',
        ])->assertRedirect();
        $meeting = MeetingEvent::where('event_name', 'QA Meeting')->firstOrFail();
        $this->assertSame('RESERVED', $activeRoom->refresh()->operational_status->value);

        $this->actingAs($user)->post('/packages', [
            'code' => 'QA-PACK',
            'name' => 'QA Package',
            'price' => 125000,
            'is_active' => true,
            'entitlement_type' => 'COFFEE_BREAK',
            'entitlement_quantity' => 1,
        ])->assertRedirect();
        $package = MeetingPackage::where('code', 'QA-PACK')->firstOrFail();
        $this->assertCount(1, $package->entitlements);

        $this->actingAs($user)->post('/participants', [
            'meeting_event_id' => $meeting->id,
            'full_name' => 'QA Participant',
            'email' => 'participant@example.test',
            'phone' => '+628121222222',
            'identity_reference' => 'QA-ID-1',
        ])->assertRedirect();
        $participant = Participant::where('full_name', 'QA Participant')->firstOrFail();
        $this->actingAs($user)->put('/participants/'.$participant->id, [
            'full_name' => 'QA Participant Updated',
            'email' => 'participant@example.test',
            'phone' => '+628121222222',
            'identity_reference' => 'QA-ID-1',
            'status' => 'REGISTERED',
        ])->assertRedirect();
    }

    public function test_tenant_isolation_blocks_cross_hotel_resources_and_relations(): void
    {
        $this->seed();
        [$hotelA, $userA] = $this->hotelUser('ORIA');
        [$hotelB] = $this->hotelUser('AONE-WH');

        $foreignClient = Client::withoutGlobalScope('hotel')->where('hotel_id', $hotelB->id)->firstOrFail();
        $foreignRoom = MeetingRoom::withoutGlobalScope('hotel')->where('hotel_id', $hotelB->id)->firstOrFail();
        $localBooking = Booking::where('hotel_id', $hotelA->id)->firstOrFail();

        $this->ajaxGet($userA, '/clients/'.$foreignClient->id)->assertForbidden();
        $this->actingAs($userA)->put('/bookings/'.$localBooking->id, [
            'client_id' => $foreignClient->id,
            'booking_number' => $localBooking->booking_number,
            'booking_source' => $localBooking->booking_source,
            'booking_date' => $localBooking->booking_date->toDateString(),
            'status' => 'CONFIRMED',
        ])->assertSessionHasErrors('client_id');
        $this->actingAs($userA)->post('/meetings', [
            'booking_id' => $localBooking->id,
            'meeting_room_id' => $foreignRoom->id,
            'event_name' => 'Foreign Room Attempt',
            'event_date' => '2026-09-01',
            'start_at' => '2026-09-01 09:00:00',
            'end_at' => '2026-09-01 10:00:00',
            'expected_participants' => 10,
            'actual_participants' => 0,
            'status' => 'SCHEDULED',
        ])->assertSessionHasErrors('meeting_room_id');
    }

    public function test_meeting_room_hotel_assignment_is_tenant_safe(): void
    {
        $this->seed();
        [$hotelA, $userA] = $this->hotelUser('ORIA');
        [$hotelB] = $this->hotelUser('AONE-WH');
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($userA)->post('/meeting-rooms', [
            'hotel_id' => $hotelB->id,
            'code' => 'ILLEGAL-ROOM',
            'name' => 'Illegal Room',
            'capacity' => 10,
            'operational_status' => 'AVAILABLE',
        ])->assertSessionHasErrors('hotel_id');

        $this->actingAs($userA)->post('/meeting-rooms', [
            'code' => 'OWN-ROOM',
            'name' => 'Own Room',
            'capacity' => 10,
            'operational_status' => 'AVAILABLE',
        ])->assertRedirect();
        $this->assertDatabaseHas('meeting_rooms', ['hotel_id' => $hotelA->id, 'code' => 'OWN-ROOM']);

        $this->actingAs($superAdmin)->post('/meeting-rooms', [
            'hotel_id' => $hotelB->id,
            'code' => 'SUPER-ROOM',
            'name' => 'Super Room',
            'capacity' => 20,
            'operational_status' => 'AVAILABLE',
        ])->assertRedirect();
        $this->assertDatabaseHas('meeting_rooms', ['hotel_id' => $hotelB->id, 'code' => 'SUPER-ROOM']);
    }

    public function test_client_hotel_associations_scope_booking_selection(): void
    {
        $this->seed();
        [$hotelA, $userA] = $this->hotelUser('ORIA');
        [$hotelB, $userB] = $this->hotelUser('AONE-WH');
        $shared = Client::where('hotel_id', $hotelA->id)->firstOrFail();
        $hotelBOnly = Client::create([
            'hotel_id' => $hotelB->id,
            'external_id' => 'AONE-ONLY',
            'company_name' => 'AONE Only Client',
        ]);
        $hotelBOnly->hotels()->syncWithoutDetaching([
            $hotelB->id => ['status' => 'ACTIVE', 'metadata' => json_encode(['source' => 'test'])],
        ]);

        $shared->hotels()->syncWithoutDetaching([
            $hotelA->id => ['status' => 'ACTIVE', 'metadata' => json_encode(['source' => 'test'])],
            $hotelB->id => ['status' => 'ACTIVE', 'metadata' => json_encode(['source' => 'test'])],
        ]);

        $this->actingAs($userA)->get('/clients')->assertRedirect('/redirect')->assertSessionHas('Redirect', 'clients');
        $this->ajaxGet($userA, '/clients')->assertOk()->assertSee($shared->company_name)->assertDontSee($hotelBOnly->company_name);
        $this->ajaxGet($userB, '/clients')->assertOk()->assertSee($shared->company_name)->assertSee($hotelBOnly->company_name);

        $this->actingAs($userA)->post('/bookings', [
            'client_id' => $hotelBOnly->id,
            'booking_number' => 'BAD-CLIENT-BKG',
            'booking_source' => 'DIRECT',
            'booking_date' => '2026-08-01',
            'status' => 'CONFIRMED',
        ])->assertSessionHasErrors('client_id');

        $this->actingAs($userB)->post('/bookings', [
            'client_id' => $shared->id,
            'booking_number' => 'SHARED-CLIENT-BKG',
            'booking_source' => 'DIRECT',
            'booking_date' => '2026-08-01',
            'status' => 'CONFIRMED',
        ])->assertRedirect();
        $this->assertDatabaseHas('bookings', ['hotel_id' => $hotelB->id, 'client_id' => $shared->id, 'booking_number' => 'SHARED-CLIENT-BKG']);

        $this->assertSame(1, DB::table('client_hotel')->where('client_id', $shared->id)->where('hotel_id', $hotelB->id)->count());
    }

    public function test_booking_wizard_creates_meeting_package_assignment_and_qr_on_confirmation(): void
    {
        $this->seed();
        [$hotel, $user] = $this->hotelUser('ORIA');
        $client = Client::create([
            'hotel_id' => $hotel->id,
            'external_id' => 'WIZARD-CLIENT',
            'company_name' => 'Wizard Client',
        ]);
        $client->hotels()->syncWithoutDetaching([
            $hotel->id => ['status' => 'ACTIVE', 'metadata' => json_encode(['source' => 'test'])],
        ]);
        $room = MeetingRoom::create([
            'hotel_id' => $hotel->id,
            'code' => 'WIZARD-ROOM',
            'name' => 'Wizard Room',
            'capacity' => 40,
            'operational_status' => 'AVAILABLE',
        ]);
        $package = MeetingPackage::where('hotel_id', $hotel->id)->where('is_active', true)->firstOrFail();

        $this->actingAs($user)->post('/bookings', [
            'client_id' => $client->id,
            'booking_source' => 'DIRECT',
            'booking_date' => '2026-09-10',
            'status' => 'DRAFT',
            'event_name' => 'Wizard Booking Meeting',
            'event_date' => '2026-09-10',
            'start_at' => '2026-09-10 09:00:00',
            'end_at' => '2026-09-10 12:00:00',
            'meeting_room_id' => $room->id,
            'package_id' => $package->id,
            'expected_participants' => 25,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $meeting = MeetingEvent::where('event_name', 'Wizard Booking Meeting')->firstOrFail();
        $booking = $meeting->booking()->firstOrFail();
        $this->assertStringStartsWith('BKG-', $booking->booking_number);

        $this->assertSame($room->id, $meeting->meeting_room_id);
        $this->assertDatabaseHas('meeting_package_assignments', [
            'meeting_event_id' => $meeting->id,
            'package_id' => $package->id,
            'participant_quota' => 25,
        ]);
        $this->assertNull($meeting->meeting_qr_token_hash);

        $this->actingAs($user)->post(route('bookings.status', $booking), ['status' => 'CONFIRMED'])->assertRedirect();
        $meeting->refresh();
        $this->assertSame('SCHEDULED', $meeting->status->value);
        $this->assertNotNull($meeting->meeting_qr_token_hash);
        $this->assertNotNull($meeting->meeting_qr_path);

        $this->ajaxGet($user, '/meetings/create')
            ->assertOk()
            ->assertSee('Meeting schedules are now processed from booking data')
            ->assertDontSee($booking->booking_number);
        $this->actingAs($user)->post('/meetings', ['booking_id' => $booking->id])
            ->assertRedirect(route('meetings.show', $meeting));
        $this->assertSame(1, MeetingEvent::where('booking_id', $booking->id)->count());
    }

    public function test_front_office_can_use_meeting_attendance_operations(): void
    {
        $this->seed();
        $hotel = Hotel::where('code', 'ORIA')->firstOrFail();
        $fo = User::where('hotel_id', $hotel->id)->where('username', 'oria.fo')->firstOrFail();
        $meeting = MeetingEvent::where('hotel_id', $hotel->id)->where('status', 'SCHEDULED')->firstOrFail();

        $this->ajaxGet($fo, '/meetings?date='.$meeting->event_date->toDateString())
            ->assertOk()
            ->assertSee('Meeting Attendance Operations')
            ->assertSee($meeting->event_name);

        $this->ajaxGet($fo, '/meetings/'.$meeting->id)
            ->assertOk()
            ->assertSee('data-toggle="tab"', false)
            ->assertSee('id="meeting-attendance"', false)
            ->assertSee('Checked In');

        $this->ajaxGet($fo, '/meetings/'.$meeting->id.'/edit')
            ->assertOk()
            ->assertSee('Edit Meeting');

        $this->actingAs($fo)
            ->post(route('meetings.transition', $meeting), ['status' => 'CHECKIN_OPEN'])
            ->assertRedirect(route('meetings.show', $meeting));

        $this->assertSame('CHECKIN_OPEN', $meeting->refresh()->status->value);
    }

    public function test_participant_list_can_filter_by_meeting_client_and_date(): void
    {
        $this->seed();
        [$hotel, $user] = $this->hotelUser('ORIA');
        $room = MeetingRoom::where('hotel_id', $hotel->id)->firstOrFail();

        $clientA = Client::create(['hotel_id' => $hotel->id, 'external_id' => 'PF-A', 'company_name' => 'Participant Filter Client A']);
        $clientB = Client::create(['hotel_id' => $hotel->id, 'external_id' => 'PF-B', 'company_name' => 'Participant Filter Client B']);
        $clientA->hotels()->syncWithoutDetaching([$hotel->id => ['status' => 'ACTIVE']]);
        $clientB->hotels()->syncWithoutDetaching([$hotel->id => ['status' => 'ACTIVE']]);

        $bookingA = Booking::create(['hotel_id' => $hotel->id, 'client_id' => $clientA->id, 'booking_number' => 'PF-A-BOOK', 'booking_source' => 'DIRECT', 'booking_date' => '2026-10-01', 'status' => 'CONFIRMED']);
        $bookingB = Booking::create(['hotel_id' => $hotel->id, 'client_id' => $clientB->id, 'booking_number' => 'PF-B-BOOK', 'booking_source' => 'DIRECT', 'booking_date' => '2026-10-02', 'status' => 'CONFIRMED']);

        $meetingA = MeetingEvent::create([
            'hotel_id' => $hotel->id,
            'booking_id' => $bookingA->id,
            'meeting_room_id' => $room->id,
            'event_name' => 'Participant Filter Meeting A',
            'event_date' => '2026-10-01',
            'start_at' => '2026-10-01 09:00:00',
            'end_at' => '2026-10-01 11:00:00',
            'expected_participants' => 10,
            'status' => 'SCHEDULED',
        ]);
        $meetingB = MeetingEvent::create([
            'hotel_id' => $hotel->id,
            'booking_id' => $bookingB->id,
            'meeting_room_id' => $room->id,
            'event_name' => 'Participant Filter Meeting B',
            'event_date' => '2026-10-02',
            'start_at' => '2026-10-02 09:00:00',
            'end_at' => '2026-10-02 11:00:00',
            'expected_participants' => 10,
            'status' => 'SCHEDULED',
        ]);

        Participant::create(['hotel_id' => $hotel->id, 'meeting_event_id' => $meetingA->id, 'participant_number' => 'PF-A-001', 'full_name' => 'Participant Filter Alice', 'registered_at' => now(), 'status' => 'REGISTERED']);
        Participant::create(['hotel_id' => $hotel->id, 'meeting_event_id' => $meetingB->id, 'participant_number' => 'PF-B-001', 'full_name' => 'Participant Filter Bob', 'registered_at' => now(), 'status' => 'REGISTERED']);

        $this->ajaxGet($user, route('participants.index', ['meeting_event_id' => $meetingA->id]))
            ->assertOk()
            ->assertSee('Participant Filter Alice')
            ->assertDontSee('Participant Filter Bob');

        $this->ajaxGet($user, route('participants.index', ['client_id' => $clientB->id]))
            ->assertOk()
            ->assertSee('Participant Filter Bob')
            ->assertDontSee('Participant Filter Alice');

        $this->ajaxGet($user, route('participants.index', ['meeting_date' => '2026-10-01']))
            ->assertOk()
            ->assertSee('Participant Filter Alice')
            ->assertDontSee('Participant Filter Bob');
    }

    public function test_package_form_accepts_multiple_entitlements_and_gm_can_access_reports(): void
    {
        $this->seed();
        [, $user] = $this->hotelUser('ORIA');

        $this->actingAs($user)->post('/packages', [
            'code' => 'ORIA-MULTI',
            'name' => 'Oria Multi Entitlement Package',
            'price' => 250000,
            'is_active' => true,
            'entitlements' => [
                ['type' => 'COFFEE_BREAK', 'quantity' => 2, 'notes' => 'Morning and afternoon'],
                ['type' => 'LUNCH', 'quantity' => 1, 'notes' => 'Buffet lunch'],
            ],
        ])->assertRedirect();

        $package = MeetingPackage::where('code', 'ORIA-MULTI')->firstOrFail();
        $this->assertSame(2, PackageEntitlement::where('package_id', $package->id)->count());
        $this->assertDatabaseHas('package_entitlements', ['package_id' => $package->id, 'entitlement_type' => 'COFFEE_BREAK', 'quantity' => 2]);
        $this->assertDatabaseHas('package_entitlements', ['package_id' => $package->id, 'entitlement_type' => 'LUNCH', 'quantity' => 1]);

        $gm = User::role('General Manager')->firstOrFail();
        $this->assertTrue($gm->can('Report'));
        $this->assertTrue($gm->can('Meeting Report'));
        $this->assertTrue($gm->can('report.view'));
        $this->assertTrue($gm->can('report.export'));
    }

    public function test_attendance_duplicate_checkin_is_prevented(): void
    {
        $this->seed();
        $meeting = MeetingEvent::firstOrFail();
        $participant = Participant::create([
            'hotel_id' => $meeting->hotel_id,
            'meeting_event_id' => $meeting->id,
            'participant_number' => 'DUP-ATT-'.uniqid(),
            'full_name' => 'Duplicate Attendance Test',
            'registered_at' => now(),
            'status' => 'REGISTERED',
        ]);

        MeetingAttendance::create([
            'meeting_event_id' => $participant->meeting_event_id,
            'participant_id' => $participant->id,
            'attendance_type' => AttendanceType::MEETING_CHECKIN,
            'attended_at' => now(),
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        MeetingAttendance::create([
            'meeting_event_id' => $participant->meeting_event_id,
            'participant_id' => $participant->id,
            'attendance_type' => AttendanceType::MEETING_CHECKIN,
            'attended_at' => now(),
        ]);
    }

    public function test_super_admin_switcher_ui_visibility_and_invalid_hotel_rejection(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();
        [, $normalUser] = $this->hotelUser();

        $this->actingAs($superAdmin)->get('/tenant-switch')->assertRedirect('/redirect')->assertSessionHas('Redirect', 'tenant-switch');
        $this->ajaxGet($superAdmin, '/tenant-switch')->assertOk()->assertSee('Tenant Context');
        $this->actingAs($normalUser)->get('/tenant-switch')->assertForbidden();
        $this->actingAs($superAdmin)->post('/tenant-switch', ['hotel_id' => 999999])->assertSessionHasErrors('hotel_id');

        $inactive = Hotel::where('status', 'ACTIVE')->firstOrFail();
        $active = Hotel::where('status', 'ACTIVE')->whereKeyNot($inactive->id)->firstOrFail();
        $inactive->update(['status' => 'INACTIVE']);
        $this->actingAs($superAdmin)
            ->withSession(['tenant_hotel_id' => $active->id])
            ->post('/tenant-switch', ['hotel_id' => $inactive->id])
            ->assertSessionHasErrors('hotel_id')
            ->assertSessionHas('tenant_hotel_id', $active->id);
    }

    public function test_phase_three_migration_command_modes(): void
    {
        $this->seed();

        $this->artisan('headcounter:migrate-phase-three-domain --dry-run')->assertExitCode(0);
        $this->artisan('headcounter:migrate-phase-three-domain --validate-only')->assertExitCode(0);
        $this->artisan('headcounter:migrate-phase-three-domain --resume')->assertExitCode(0);
    }

    private function hotelUser(string $hotelCode = 'ORIA'): array
    {
        $hotel = Hotel::where('code', $hotelCode)->firstOrFail();
        $user = User::where('hotel_id', $hotel->id)->where('username', strtolower(str_replace('-', '', $hotelCode)).'.admin')->firstOrFail();

        return [$hotel, $user];
    }

    private function ajaxGet(User $user, string $uri)
    {
        return $this->actingAs($user)->withHeader('X-Requested-With', 'XMLHttpRequest')->get($uri);
    }
}
