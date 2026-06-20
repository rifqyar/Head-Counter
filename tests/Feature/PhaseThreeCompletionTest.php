<?php

namespace Tests\Feature;

use App\Domain\Attendance\MeetingAttendance;
use App\Domain\Booking\Booking;
use App\Domain\Booking\Client;
use App\Domain\Catering\MeetingPackage;
use App\Domain\Hotel\Hotel;
use App\Domain\Meeting\MeetingEvent;
use App\Domain\Meeting\MeetingRoom;
use App\Domain\Participant\Participant;
use App\Enums\AttendanceType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseThreeCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_canonical_crud_ui_paths_for_core_resources(): void
    {
        $this->seed();
        [$hotel, $user] = $this->hotelUser();

        $this->actingAs($user)->get('/meeting-rooms/create')->assertOk()->assertSee('Create Meeting Room');
        $this->actingAs($user)->post('/meeting-rooms', [
            'code' => 'QA-ROOM',
            'name' => 'QA Room',
            'floor' => '3',
            'capacity' => 30,
            'operational_status' => 'AVAILABLE',
        ])->assertRedirect();
        $room = MeetingRoom::where('code', 'QA-ROOM')->firstOrFail();
        $this->actingAs($user)->get('/meeting-rooms/'.$room->id)->assertOk()->assertSee('QA Room');
        $this->actingAs($user)->put('/meeting-rooms/'.$room->id, [
            'code' => 'QA-ROOM',
            'name' => 'QA Room Updated',
            'floor' => '3',
            'capacity' => 32,
            'operational_status' => 'AVAILABLE',
        ])->assertRedirect();
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

        $this->actingAs($userA)->get('/clients/'.$foreignClient->id)->assertForbidden();
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

    public function test_attendance_duplicate_checkin_is_prevented(): void
    {
        $this->seed();
        $participant = Participant::firstOrFail();

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

        $this->actingAs($superAdmin)->get('/tenant-switch')->assertOk()->assertSee('Tenant Context');
        $this->actingAs($normalUser)->get('/tenant-switch')->assertForbidden();
        $this->actingAs($superAdmin)->post('/tenant-switch', ['hotel_id' => 999999])->assertSessionHasErrors('hotel_id');
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
}
