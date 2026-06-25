<?php

namespace Tests\Feature;

use App\Domain\Booking\Booking;
use App\Domain\Booking\Client;
use App\Domain\Catering\MealSession;
use App\Domain\Catering\MeetingPackage;
use App\Domain\Catering\MeetingPackageAssignment;
use App\Domain\Catering\PackageEntitlement;
use App\Domain\Hotel\Hotel;
use App\Domain\Meeting\MeetingEvent;
use App\Domain\Meeting\MeetingRoom;
use App\Enums\MealSessionStatus;
use App\Enums\MeetingStatus;
use App\Enums\RoomOperationalStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class TmpScannerAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_real_login_then_api_call_with_referer(): void
    {
        [$hotel, $meeting, $session, $token, $scanner] = $this->fixture();

        $login = $this->post('/login', [
            'username' => $scanner->username,
            'password' => 'password',
        ]);
        echo "\n[login] status: ".$login->status()."\n";

        $payload = [
            'qr_token' => $token,
            'meal_session_id' => $session->id,
            'device_id' => 'test-device',
            'idempotency_key' => 'key-1',
        ];

        $response = $this->withHeaders(['Referer' => 'https://localhost/scanner', 'Origin' => 'https://localhost'])
            ->postJson('/api/v1/scanner/redeem', $payload);

        echo "\n[real login + referer] status: ".$response->status()."\n";
        echo $response->content()."\n";
        $this->assertTrue(true);
    }

    public function test_real_login_then_api_call_without_referer(): void
    {
        [$hotel, $meeting, $session, $token, $scanner] = $this->fixture();

        $this->post('/login', [
            'username' => $scanner->username,
            'password' => 'password',
        ]);

        $payload = [
            'qr_token' => $token,
            'meal_session_id' => $session->id,
            'device_id' => 'test-device',
            'idempotency_key' => 'key-2',
        ];

        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class;
        $ref = new \ReflectionMethod(\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class, 'fromFrontend');
        $ref->setAccessible(true);
        $req = \Illuminate\Http\Request::create('/api/v1/scanner/redeem', 'POST');
        echo "\n[fromFrontend no headers] ".var_export($ref->invoke(null, $req), true)."\n";

        $response = $this->postJson('/api/v1/scanner/redeem', $payload);

        echo "\n[real login + NO referer] status: ".$response->status()."\n";
        echo $response->content()."\n";
        $this->assertTrue(true);
    }

    private function fixture(): array
    {
        $hotel = Hotel::create(['code' => 'H'.uniqid(), 'name' => 'Tmp Hotel']);
        $client = Client::create(['hotel_id' => $hotel->id, 'company_name' => 'Tmp Client']);
        $booking = Booking::create(['hotel_id' => $hotel->id, 'client_id' => $client->id, 'booking_number' => 'BKG-'.uniqid(), 'booking_source' => 'TEST', 'booking_date' => '2026-07-01', 'status' => 'CONFIRMED']);
        $room = MeetingRoom::create(['hotel_id' => $hotel->id, 'code' => 'ROOM-'.uniqid(), 'name' => 'Tmp Room', 'operational_status' => RoomOperationalStatus::AVAILABLE]);
        $meeting = MeetingEvent::create([
            'hotel_id' => $hotel->id, 'booking_id' => $booking->id, 'meeting_room_id' => $room->id,
            'event_name' => 'Tmp Meeting', 'event_date' => '2026-07-01',
            'start_at' => now()->subHour(), 'end_at' => now()->addHours(2),
            'expected_participants' => 10, 'status' => MeetingStatus::SCHEDULED,
            'checkin_open_at' => now()->subHour(), 'checkin_close_at' => now()->addHour(),
        ]);
        $package = MeetingPackage::create(['hotel_id' => $hotel->id, 'code' => 'PCK-'.uniqid(), 'name' => 'Half Day', 'price' => 100000]);
        PackageEntitlement::create(['package_id' => $package->id, 'entitlement_type' => 'COFFEE_BREAK', 'quantity' => 1]);
        MeetingPackageAssignment::create(['meeting_event_id' => $meeting->id, 'package_id' => $package->id, 'participant_quota' => 10, 'unit_price' => 100000]);

        $session = MealSession::create([
            'hotel_id' => $hotel->id, 'meeting_event_id' => $meeting->id,
            'entitlement_type' => 'COFFEE_BREAK', 'session_number' => 1,
            'name' => 'Coffee Break 1', 'status' => MealSessionStatus::OPEN,
        ]);

        $result = app(\App\Actions\RegisterParticipantAction::class)->executeWithQr($meeting, [
            'full_name' => 'Tmp Guest', 'email' => 'tmp@example.test', 'phone' => '+6281200000000',
        ]);

        Permission::findOrCreate('redemption.scan', 'web');
        $scanner = User::create([
            'hotel_id' => $hotel->id, 'name' => 'Scanner', 'username' => 'scanner'.uniqid(),
            'email' => 'scanner'.uniqid().'@example.test', 'password' => 'password',
        ]);
        $scanner->givePermissionTo('redemption.scan');

        return [$hotel, $meeting, $session, $result['participant_qr_token'], $scanner];
    }
}
