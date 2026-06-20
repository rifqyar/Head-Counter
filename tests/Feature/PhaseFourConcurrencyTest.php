<?php

namespace Tests\Feature;

use App\Actions\RegisterParticipantAction;
use App\Domain\Booking\Booking;
use App\Domain\Booking\Client;
use App\Domain\Catering\MealSession;
use App\Domain\Catering\MeetingPackage;
use App\Domain\Catering\MeetingPackageAssignment;
use App\Domain\Catering\PackageEntitlement;
use App\Domain\Hotel\Hotel;
use App\Domain\Meeting\MeetingEvent;
use App\Domain\Meeting\MeetingRoom;
use App\Domain\Redemption\ParticipantEntitlement;
use App\Domain\Redemption\Redemption;
use App\Enums\MealSessionStatus;
use App\Enums\MeetingStatus;
use App\Enums\RedemptionStatus;
use App\Enums\RoomOperationalStatus;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class PhaseFourConcurrencyTest extends TestCase
{
    public function test_true_postgresql_concurrent_redemption_allows_exactly_one_success(): void
    {
        $this->artisan('migrate:fresh', ['--force' => true])->run();
        $this->assertSame('pgsql', DB::connection()->getDriverName());

        [$hotel, $meeting, $session, $token, $scanner] = $this->redeemFixture();
        $participantId = DB::table('participants')->where('full_name', 'Concurrent Guest')->value('id');
        $payloads = [
            [
                'qr_token' => $token,
                'meal_session_id' => $session->id,
                'device_id' => 'race-worker-a',
                'idempotency_key' => 'race-key-a',
            ],
            [
                'qr_token' => $token,
                'meal_session_id' => $session->id,
                'device_id' => 'race-worker-b',
                'idempotency_key' => 'race-key-b',
            ],
        ];
        $barrier = storage_path('framework/testing/concurrency-'.uniqid());
        mkdir($barrier, 0777, true);

        $workers = [
            new Process([PHP_BINARY, 'artisan', 'scanner:concurrent-redemption-worker', json_encode($payloads[0]), (string) $scanner->id, (string) $hotel->id, $barrier, 'a'], base_path()),
            new Process([PHP_BINARY, 'artisan', 'scanner:concurrent-redemption-worker', json_encode($payloads[1]), (string) $scanner->id, (string) $hotel->id, $barrier, 'b'], base_path()),
        ];

        foreach ($workers as $worker) {
            $worker->setTimeout(40);
            $worker->start();
        }

        $deadline = microtime(true) + 20;
        while ((! file_exists($barrier.DIRECTORY_SEPARATOR.'a.ready') || ! file_exists($barrier.DIRECTORY_SEPARATOR.'b.ready')) && microtime(true) < $deadline) {
            usleep(20000);
        }
        $this->assertFileExists($barrier.DIRECTORY_SEPARATOR.'a.ready');
        $this->assertFileExists($barrier.DIRECTORY_SEPARATOR.'b.ready');

        file_put_contents($barrier.DIRECTORY_SEPARATOR.'release', '1');

        foreach ($workers as $worker) {
            $worker->wait();
            $this->assertTrue($worker->isSuccessful(), $worker->getErrorOutput().$worker->getOutput());
        }

        $results = array_map(fn (Process $worker) => json_decode(trim($worker->getOutput()), true, flags: JSON_THROW_ON_ERROR), $workers);
        $statuses = array_column($results, 'status');
        $bodies = array_column($results, 'body');

        $this->assertSame(1, count(array_filter($statuses, fn ($status) => $status === 200)), 'successful_response_count');
        $this->assertSame(1, count(array_filter($statuses, fn ($status) => in_array($status, [409, 422], true))), 'rejected_response_count');
        $this->assertContains(($bodies[0]['rejection_code'] ?? $bodies[1]['rejection_code']), ['ALREADY_REDEEMED', 'DUPLICATE_REQUEST']);
        $this->assertSame(1, Redemption::withoutGlobalScope('hotel')->where('participant_id', $participantId)->where('meal_session_id', $session->id)->where('status', RedemptionStatus::SUCCESS->value)->count(), 'active_success_redemption_count');

        $entitlement = ParticipantEntitlement::where('participant_id', $participantId)->firstOrFail();
        $this->assertSame(1, $entitlement->redeemed_quantity, 'redeemed_quantity_increment');
        $this->assertSame(0, $entitlement->remaining_quantity, 'remaining_quantity_decrement');
        $this->assertGreaterThanOrEqual(0, $entitlement->remaining_quantity);
        $this->assertCount(2, DB::table('scanner_idempotency_keys')->where('hotel_id', $hotel->id)->get());
        $this->assertDatabaseHas('audit_logs', ['event' => 'redemption.succeeded']);
    }

    private function redeemFixture(): array
    {
        [$hotel, $meeting] = $this->meetingFixture();
        $this->app->make(TenantContext::class)->set($hotel);
        $session = MealSession::create([
            'hotel_id' => $hotel->id,
            'meeting_event_id' => $meeting->id,
            'entitlement_type' => 'COFFEE_BREAK',
            'session_number' => 1,
            'name' => 'Coffee Break 1',
            'status' => MealSessionStatus::OPEN,
        ]);

        $result = app(RegisterParticipantAction::class)->executeWithQr($meeting, [
            'full_name' => 'Concurrent Guest',
            'email' => 'concurrent.guest@example.test',
            'phone' => '+6281299999900',
        ]);

        return [$hotel, $meeting, $session, $result['participant_qr_token'], $this->scannerUser($hotel)];
    }

    private function meetingFixture(): array
    {
        $hotel = Hotel::create(['code' => 'H'.uniqid(), 'name' => 'Concurrency Hotel']);
        $client = Client::create(['hotel_id' => $hotel->id, 'company_name' => 'Concurrency Client']);
        $booking = Booking::create(['hotel_id' => $hotel->id, 'client_id' => $client->id, 'booking_number' => 'BKG-'.uniqid(), 'booking_source' => 'TEST', 'booking_date' => '2026-07-01', 'status' => 'CONFIRMED']);
        $room = MeetingRoom::create(['hotel_id' => $hotel->id, 'code' => 'ROOM-'.uniqid(), 'name' => 'Concurrency Room', 'operational_status' => RoomOperationalStatus::AVAILABLE]);
        $meeting = MeetingEvent::create([
            'hotel_id' => $hotel->id,
            'booking_id' => $booking->id,
            'meeting_room_id' => $room->id,
            'event_name' => 'Concurrency Meeting',
            'event_date' => '2026-07-01',
            'start_at' => now()->subHour(),
            'end_at' => now()->addHours(2),
            'expected_participants' => 10,
            'status' => MeetingStatus::SCHEDULED,
            'checkin_open_at' => now()->subHour(),
            'checkin_close_at' => now()->addHour(),
        ]);
        $package = MeetingPackage::create(['hotel_id' => $hotel->id, 'code' => 'PCK-'.uniqid(), 'name' => 'Half Day', 'price' => 100000]);
        PackageEntitlement::create(['package_id' => $package->id, 'entitlement_type' => 'COFFEE_BREAK', 'quantity' => 1]);
        MeetingPackageAssignment::create(['meeting_event_id' => $meeting->id, 'package_id' => $package->id, 'participant_quota' => 10, 'unit_price' => 100000]);

        return [$hotel, $meeting];
    }

    private function scannerUser(Hotel $hotel): User
    {
        Permission::findOrCreate('redemption.scan', 'web');
        $user = User::create([
            'hotel_id' => $hotel->id,
            'name' => 'Scanner User',
            'username' => 'scanner'.uniqid(),
            'email' => 'scanner'.uniqid().'@example.test',
            'password' => 'password',
        ]);
        $user->givePermissionTo('redemption.scan');

        return $user;
    }
}
