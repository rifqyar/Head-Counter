<?php

namespace Tests\Feature;

use App\Actions\CreateMeetingEventAction;
use App\Actions\RegisterParticipantAction;
use App\Actions\TransitionMeetingStatusAction;
use App\Domain\Booking\Booking;
use App\Domain\Booking\Client;
use App\Domain\Hotel\Hotel;
use App\Domain\Meeting\MeetingEvent;
use App\Domain\Meeting\MeetingRoom;
use App\Enums\MeetingStatus;
use App\Enums\RoomOperationalStatus;
use App\Exceptions\DomainException;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseThreeDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_hotel_user_only_lists_own_rooms(): void
    {
        [$hotelA, $hotelB, $userA] = $this->tenantFixture();

        MeetingRoom::create([
            'hotel_id' => $hotelA->id,
            'code' => 'A-ROOM',
            'name' => 'Hotel A Room',
            'operational_status' => RoomOperationalStatus::AVAILABLE,
        ]);
        MeetingRoom::create([
            'hotel_id' => $hotelB->id,
            'code' => 'B-ROOM',
            'name' => 'Hotel B Room',
            'operational_status' => RoomOperationalStatus::AVAILABLE,
        ]);

        $response = $this->actingAs($userA)->getJson('/meeting-rooms');

        $response->assertOk();
        $response->assertSee('Hotel A Room');
        $response->assertDontSee('Hotel B Room');
    }

    public function test_hotel_user_cannot_view_other_hotel_room(): void
    {
        [$hotelA, $hotelB, $userA] = $this->tenantFixture();
        $this->app->make(TenantContext::class)->set($hotelA);

        $room = MeetingRoom::create([
            'hotel_id' => $hotelB->id,
            'code' => 'B-ROOM-2',
            'name' => 'Hotel B Private Room',
            'operational_status' => RoomOperationalStatus::AVAILABLE,
        ]);

        $this->actingAs($userA)->get('/meeting-rooms/'.$room->id)->assertNotFound();
    }

    public function test_room_conflict_service_rejects_overlaps_but_allows_adjacent_meetings(): void
    {
        [$hotel] = $this->tenantFixture();
        $this->app->make(TenantContext::class)->set($hotel);
        [$booking, $room] = $this->bookingAndRoom($hotel);
        $action = app(CreateMeetingEventAction::class);

        $action->execute($this->meetingPayload($hotel, $booking, $room, 'Base', '2026-07-01 09:00:00', '2026-07-01 10:00:00'));

        $this->expectException(DomainException::class);
        $action->execute($this->meetingPayload($hotel, $booking, $room, 'Overlap', '2026-07-01 09:30:00', '2026-07-01 10:30:00'));
    }

    public function test_database_exclusion_constraint_rejects_concurrent_overlap(): void
    {
        [$hotel] = $this->tenantFixture();
        $this->app->make(TenantContext::class)->set($hotel);
        [$booking, $room] = $this->bookingAndRoom($hotel);

        MeetingEvent::create($this->meetingPayload($hotel, $booking, $room, 'Base DB', '2026-07-02 09:00:00', '2026-07-02 10:00:00'));

        $this->expectException(QueryException::class);
        MeetingEvent::create($this->meetingPayload($hotel, $booking, $room, 'Overlap DB', '2026-07-02 09:15:00', '2026-07-02 09:45:00'));
    }

    public function test_adjacent_and_cancelled_meetings_do_not_conflict(): void
    {
        [$hotel] = $this->tenantFixture();
        $this->app->make(TenantContext::class)->set($hotel);
        [$booking, $room] = $this->bookingAndRoom($hotel);

        MeetingEvent::create($this->meetingPayload($hotel, $booking, $room, 'Base', '2026-07-03 09:00:00', '2026-07-03 10:00:00'));
        MeetingEvent::create($this->meetingPayload($hotel, $booking, $room, 'Adjacent', '2026-07-03 10:00:00', '2026-07-03 11:00:00'));
        MeetingEvent::create(array_merge(
            $this->meetingPayload($hotel, $booking, $room, 'Cancelled', '2026-07-03 09:15:00', '2026-07-03 09:45:00'),
            ['status' => MeetingStatus::CANCELLED]
        ));

        $this->assertSame(3, MeetingEvent::withoutGlobalScope('hotel')->where('hotel_id', $hotel->id)->count());
    }

    public function test_meeting_state_transitions_are_explicit_and_update_room_status(): void
    {
        [$hotel] = $this->tenantFixture();
        $this->app->make(TenantContext::class)->set($hotel);
        [$booking, $room] = $this->bookingAndRoom($hotel);
        $meeting = MeetingEvent::create($this->meetingPayload($hotel, $booking, $room, 'Lifecycle', '2026-07-04 09:00:00', '2026-07-04 10:00:00'));
        $action = app(TransitionMeetingStatusAction::class);

        $meeting = $action->execute($meeting, MeetingStatus::OCCUPIED);
        $this->assertSame(MeetingStatus::OCCUPIED, $meeting->status);
        $this->assertNotNull($meeting->started_at);
        $this->assertSame(RoomOperationalStatus::OCCUPIED, $room->refresh()->operational_status);

        $meeting = $action->execute($meeting, MeetingStatus::COMPLETED);
        $this->assertSame(MeetingStatus::COMPLETED, $meeting->status);
        $this->assertNotNull($meeting->completed_at);
        $this->assertSame(RoomOperationalStatus::CLEANING, $room->refresh()->operational_status);

        $this->expectException(DomainException::class);
        $action->execute($meeting, MeetingStatus::SCHEDULED);
    }

    public function test_participant_duplicate_detection_uses_email_phone_or_identity(): void
    {
        [$hotel] = $this->tenantFixture();
        $this->app->make(TenantContext::class)->set($hotel);
        [$booking, $room] = $this->bookingAndRoom($hotel);
        $meeting = MeetingEvent::create($this->meetingPayload($hotel, $booking, $room, 'Participants', '2026-07-05 09:00:00', '2026-07-05 10:00:00'));
        $action = app(RegisterParticipantAction::class);

        $action->execute($meeting, [
            'full_name' => 'Jane Participant',
            'email' => 'Jane@example.test',
            'phone' => '0812-333-444',
            'identity_reference' => 'ID-1',
        ]);

        $this->expectException(DomainException::class);
        $action->execute($meeting, [
            'full_name' => 'Jane Again',
            'email' => ' jane@EXAMPLE.test ',
        ]);
    }

    public function test_phase_three_seeder_creates_real_jakarta_hotels_and_hotel_users(): void
    {
        $this->seed();

        $this->assertDatabaseHas('hotels', [
            'code' => 'ORIA',
            'name' => 'Oria Hotel Jakarta',
        ]);
        $this->assertDatabaseHas('hotels', [
            'code' => 'ASHLEY-WH',
            'name' => 'Ashley Hotel Wahid Hasyim',
        ]);
        $this->assertDatabaseHas('hotels', [
            'code' => 'AONE-WH',
            'name' => 'AONE Hotel Jakarta',
        ]);
        $this->assertDatabaseHas('hotels', [
            'code' => 'MORRISSEY',
            'name' => 'Morrissey Hotel Residences',
        ]);

        $superAdmin = User::where('username', 'superadmin')->firstOrFail();
        $this->assertTrue($superAdmin->isSuperAdmin());
        $this->assertNull($superAdmin->hotel_id);

        foreach (['oria', 'ashleywh', 'aonewh', 'morrissey'] as $slug) {
            $this->assertDatabaseHas('users', ['username' => $slug.'.gm']);
            $this->assertDatabaseHas('users', ['username' => $slug.'.admin']);
            $this->assertDatabaseHas('users', ['username' => $slug.'.fo']);
        }
    }

    public function test_super_admin_can_switch_tenant_context(): void
    {
        $this->seed();

        $oria = Hotel::where('code', 'ORIA')->firstOrFail();
        $aone = Hotel::where('code', 'AONE-WH')->firstOrFail();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($superAdmin)
            ->withSession(['tenant_hotel_id' => $aone->id])
            ->getJson('/meeting-rooms')
            ->assertOk()
            ->assertSee('AONE')
            ->assertDontSee('Oria');

        $this->actingAs($superAdmin)
            ->withSession(['tenant_hotel_id' => $oria->id])
            ->getJson('/meeting-rooms')
            ->assertOk()
            ->assertSee('Oria')
            ->assertDontSee('AONE');
    }

    private function tenantFixture(): array
    {
        $hotelA = Hotel::create(['code' => 'HA'.uniqid(), 'name' => 'Hotel A']);
        $hotelB = Hotel::create(['code' => 'HB'.uniqid(), 'name' => 'Hotel B']);
        $userA = User::create([
            'hotel_id' => $hotelA->id,
            'name' => 'Tenant User',
            'username' => 'tenant'.uniqid(),
            'email' => 'tenant'.uniqid().'@example.test',
            'password' => 'password',
        ]);

        return [$hotelA, $hotelB, $userA];
    }

    private function bookingAndRoom(Hotel $hotel): array
    {
        $client = Client::create([
            'hotel_id' => $hotel->id,
            'company_name' => 'Client '.$hotel->code,
        ]);
        $booking = Booking::create([
            'hotel_id' => $hotel->id,
            'client_id' => $client->id,
            'booking_number' => 'BKG-'.uniqid(),
            'booking_source' => 'TEST',
            'booking_date' => '2026-07-01',
            'status' => 'CONFIRMED',
        ]);
        $room = MeetingRoom::create([
            'hotel_id' => $hotel->id,
            'code' => 'ROOM-'.uniqid(),
            'name' => 'Room '.$hotel->code,
            'operational_status' => RoomOperationalStatus::AVAILABLE,
        ]);

        return [$booking, $room];
    }

    private function meetingPayload(Hotel $hotel, Booking $booking, MeetingRoom $room, string $name, string $startAt, string $endAt): array
    {
        return [
            'hotel_id' => $hotel->id,
            'booking_id' => $booking->id,
            'meeting_room_id' => $room->id,
            'event_name' => $name,
            'event_date' => substr($startAt, 0, 10),
            'start_at' => $startAt,
            'end_at' => $endAt,
            'expected_participants' => 10,
            'actual_participants' => 0,
            'status' => MeetingStatus::SCHEDULED,
        ];
    }
}
