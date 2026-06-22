<?php

namespace Tests\Feature;

use App\Actions\OverrideRedemptionAction;
use App\Actions\RedeemParticipantAction;
use App\Actions\RegisterParticipantAction;
use App\Actions\ReverseRedemptionAction;
use App\Domain\Booking\Booking;
use App\Domain\Booking\Client;
use App\Domain\Catering\MealSession;
use App\Domain\Catering\MeetingPackage;
use App\Domain\Catering\MeetingPackageAssignment;
use App\Domain\Catering\PackageEntitlement;
use App\Domain\Hotel\Hotel;
use App\Domain\Meeting\MeetingEvent;
use App\Domain\Meeting\MeetingRoom;
use App\Domain\QRCode\MeetingQRService;
use App\Domain\Redemption\Redemption;
use App\Enums\MealSessionStatus;
use App\Enums\MeetingStatus;
use App\Enums\RedemptionStatus;
use App\Enums\RoomOperationalStatus;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PhaseFourQRRedemptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_meeting_qr_lifecycle_stores_hash_only_and_regeneration_invalidates_old_token(): void
    {
        Storage::fake('public');
        [$hotel, $meeting] = $this->meetingFixture();
        $this->app->make(TenantContext::class)->set($hotel);
        $service = app(MeetingQRService::class);

        $issued = $service->generate($meeting);

        $this->assertNotSame($issued['token'], $meeting->refresh()->meeting_qr_token_hash);
        $this->assertSame(hash('sha256', $issued['token']), $meeting->meeting_qr_token_hash);
        $this->assertStringEndsWith('.pdf', $meeting->meeting_qr_path);
        Storage::disk('public')->assertExists($meeting->meeting_qr_path);
        $this->assertStringStartsWith('%PDF', Storage::disk('public')->get($meeting->meeting_qr_path));
        $this->assertTrue($service->validate($issued['token'])[0]);

        $regenerated = $service->regenerate($meeting);
        $this->assertFalse($service->validate($issued['token'])[0]);
        $this->assertTrue($service->validate($regenerated['token'])[0]);

        $service->revoke($meeting);
        $this->assertFalse($service->validate($regenerated['token'])[0]);
    }

    public function test_public_registration_generates_entitlements_attendance_and_participant_qr(): void
    {
        [$hotel, $meeting] = $this->meetingFixture();
        $this->app->make(TenantContext::class)->set($hotel);
        $token = app(MeetingQRService::class)->generate($meeting)['token'];

        $this->get('/attendance/meeting/'.$token)
            ->assertOk()
            ->assertSee('public-participant-wizard')
            ->assertSee('assets/plugins/bootstrap/css/bootstrap.min.css')
            ->assertSee('assets/plugins/wizard/jquery.steps.min.js')
            ->assertDontSee('@php');

        $this->post('/attendance/meeting/'.$token.'/register', [
            'full_name' => 'QR Guest',
            'email' => 'qr.guest@example.test',
            'phone' => '+6281200000001',
        ])->assertOk()
            ->assertSee('Registration Complete')
            ->assertSee('Download Participant QR PDF')
            ->assertSee('issued-card')
            ->assertSee('assets/plugins/bootstrap/css/bootstrap.min.css')
            ->assertSee('Phase Four Hotel');

        $this->assertDatabaseHas('participants', ['meeting_event_id' => $meeting->id, 'full_name' => 'QR Guest']);
        $participantId = DB::table('participants')->where('full_name', 'QR Guest')->value('id');
        $this->assertDatabaseHas('participant_entitlements', ['participant_id' => $participantId, 'entitlement_type' => 'COFFEE_BREAK', 'remaining_quantity' => 1]);
        $this->assertDatabaseHas('participant_qr_credentials', ['participant_id' => $participantId, 'status' => 'ACTIVE']);
        $this->assertDatabaseHas('meeting_attendances', ['participant_id' => $participantId, 'attendance_type' => 'MEETING_CHECKIN']);
    }

    public function test_scanner_redeem_is_idempotent_and_blocks_duplicate_session_redemption(): void
    {
        [$hotel, $meeting, $session, $token, $scanner] = $this->redeemFixture();

        $payload = [
            'qr_token' => $token,
            'meal_session_id' => $session->id,
            'device_id' => 'front-desk-1',
            'idempotency_key' => 'scan-key-1',
        ];

        $first = $this->actingAs($scanner, 'sanctum')->postJson('/api/v1/scanner/redeem', $payload)
            ->assertOk()
            ->assertJsonPath('eligible', true)
            ->assertJsonPath('participant.status', 'CHECKED_IN')
            ->json();

        $this->actingAs($scanner, 'sanctum')->postJson('/api/v1/scanner/redeem', $payload)
            ->assertOk()
            ->assertJsonPath('redemption_number', $first['redemption_number']);

        $this->actingAs($scanner, 'sanctum')->postJson('/api/v1/scanner/redeem', array_merge($payload, ['idempotency_key' => 'scan-key-2']))
            ->assertStatus(409)
            ->assertJsonPath('rejection_code', 'ALREADY_REDEEMED');

        $participantId = DB::table('participants')->where('full_name', 'Redeem Guest')->value('id');
        $this->assertSame(1, Redemption::withoutGlobalScope('hotel')->where('participant_id', $participantId)->where('meal_session_id', $session->id)->where('status', RedemptionStatus::SUCCESS->value)->count());
        $this->assertDatabaseHas('participant_entitlements', ['meeting_event_id' => $meeting->id, 'redeemed_quantity' => 1, 'remaining_quantity' => 0]);
        $this->assertDatabaseHas('participants', ['id' => $participantId, 'status' => 'CHECKED_IN']);
        $this->assertNotNull(DB::table('participants')->where('id', $participantId)->value('checked_in_at'));
    }

    public function test_scanner_validate_is_non_mutating_and_cross_hotel_session_is_rejected(): void
    {
        [$hotel, $meeting, $session, $token, $scanner] = $this->redeemFixture();
        $before = Redemption::withoutGlobalScope('hotel')->count();

        $this->actingAs($scanner, 'sanctum')->postJson('/api/v1/scanner/validate', [
            'qr_token' => $token,
            'meal_session_id' => $session->id,
            'device_id' => 'front-desk-1',
        ])->assertOk()->assertJsonPath('eligible', true);

        $this->assertSame($before, Redemption::withoutGlobalScope('hotel')->count());
        $this->assertDatabaseHas('participants', ['full_name' => 'Redeem Guest', 'status' => 'REGISTERED', 'checked_in_at' => null]);

        $otherHotel = Hotel::create(['code' => 'OTHER', 'name' => 'Other Hotel']);
        $otherUser = $this->scannerUser($otherHotel);

        $this->actingAs($otherUser, 'sanctum')->postJson('/api/v1/scanner/validate', [
            'qr_token' => $token,
            'meal_session_id' => $session->id,
        ])->assertStatus(422);
    }

    public function test_reversal_restores_entitlement_and_allows_later_redemption(): void
    {
        [$hotel, $meeting, $session, $token, $scanner] = $this->redeemFixture();
        $result = app(RedeemParticipantAction::class)->execute([
            'qr_token' => $token,
            'meal_session_id' => $session->id,
            'device_id' => 'front-desk-1',
            'idempotency_key' => 'reverse-key-1',
        ], $scanner->id, $hotel->id);

        $this->assertSame(200, $result['status']);
        $redemption = Redemption::withoutGlobalScope('hotel')->where('redemption_number', $result['body']['redemption_number'])->firstOrFail();
        app(ReverseRedemptionAction::class)->execute($redemption, $scanner->id, 'Issued in error');

        $this->assertSame(RedemptionStatus::REVERSED, $redemption->refresh()->status);
        $this->assertDatabaseHas('participant_entitlements', ['meeting_event_id' => $meeting->id, 'redeemed_quantity' => 0, 'remaining_quantity' => 1]);

        $again = app(RedeemParticipantAction::class)->execute([
            'qr_token' => $token,
            'meal_session_id' => $session->id,
            'device_id' => 'front-desk-1',
            'idempotency_key' => 'reverse-key-2',
        ], $scanner->id, $hotel->id);

        $this->assertSame(200, $again['status']);
    }

    public function test_overrideable_rejection_is_persisted_and_append_only_override_decrements_once(): void
    {
        [$hotel, $meeting, $session, $token, $scanner] = $this->redeemFixture();
        $session->update(['status' => MealSessionStatus::CLOSED]);

        $result = app(RedeemParticipantAction::class)->execute([
            'qr_token' => $token,
            'meal_session_id' => $session->id,
            'device_id' => 'front-desk-1',
            'idempotency_key' => 'reject-key-1',
        ], $scanner->id, $hotel->id);

        $this->assertSame(422, $result['status']);
        $rejected = Redemption::withoutGlobalScope('hotel')->where('idempotency_key', 'reject-key-1')->firstOrFail();
        $this->assertSame('SESSION_NOT_OPEN', $rejected->rejection_code->value);

        $session->update(['status' => MealSessionStatus::OPEN]);
        $override = app(OverrideRedemptionAction::class)->execute($rejected, $scanner->id, 'Operator confirmed service recovery');

        $this->assertSame(RedemptionStatus::REJECTED, $rejected->refresh()->status);
        $this->assertSame(RedemptionStatus::OVERRIDDEN, $override->status);
        $this->assertSame($rejected->id, $override->original_redemption_id);
        $this->assertDatabaseHas('participant_entitlements', ['meeting_event_id' => $meeting->id, 'redeemed_quantity' => 1, 'remaining_quantity' => 0]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'redemption.override_succeeded', 'auditable_id' => $override->id]);
    }

    public function test_non_overrideable_invalid_qr_remains_audit_only(): void
    {
        [$hotel, , $session, , $scanner] = $this->redeemFixture();

        $result = app(RedeemParticipantAction::class)->execute([
            'qr_token' => 'invalid-token',
            'meal_session_id' => $session->id,
            'device_id' => 'front-desk-1',
            'idempotency_key' => 'invalid-key-1',
        ], $scanner->id, $hotel->id);

        $this->assertSame(422, $result['status']);
        $this->assertSame(0, Redemption::withoutGlobalScope('hotel')->where('idempotency_key', 'invalid-key-1')->count());
        $this->assertDatabaseHas('audit_logs', ['event' => 'redemption.rejected_audit_only']);
    }

    public function test_participant_qr_admin_generate_rotate_revoke_and_one_time_display(): void
    {
        [$hotel, $meeting] = $this->meetingFixture();
        $this->app->make(TenantContext::class)->set($hotel);
        $participant = app(RegisterParticipantAction::class)->execute($meeting, [
            'full_name' => 'QR Admin Guest',
            'email' => 'qr-admin@example.test',
            'phone' => '+6281200000002',
        ]);
        $user = $this->scannerUser($hotel);
        Permission::findOrCreate('participant.qr.manage', 'web');
        $user->givePermissionTo('participant.qr.manage');

        $this->actingAs($user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('participants.qr.show', $participant))
            ->assertOk()
            ->assertSee('Participant QR Administration');
        $this->actingAs($user)->post(route('participants.qr.generate', $participant))->assertSessionHasErrors('qr');

        $oldCredential = $participant->activeQrCredential()->firstOrFail();
        $this->actingAs($user)->post(route('participants.qr.rotate', $participant), ['confirm' => '1'])->assertRedirect(route('participants.qr.show', $participant));
        $this->followingRedirects()->actingAs($user)->get(route('participants.qr.show', $participant))
            ->assertSee('Raw token')
            ->assertSee('Download QR PDF')
            ->assertSee('Reprint QR PDF')
            ->assertSee('QR Admin Guest');
        $this->actingAs($user)->get(route('participants.qr.show', $participant))->assertDontSee('Raw token:');
        $this->assertDatabaseHas('participant_qr_credentials', ['id' => $oldCredential->id, 'status' => 'REVOKED']);

        $active = $participant->fresh()->activeQrCredential()->firstOrFail();
        $this->assertNotNull($active->printable_path);
        Storage::disk('public')->assertExists($active->printable_path);
        $this->actingAs($user)->get(route('participants.qr.download-active', $participant))->assertOk();

        $this->actingAs($user)->post(route('participants.qr.revoke', $participant), ['confirm' => '1'])->assertRedirect(route('participants.qr.show', $participant));
        $this->assertDatabaseHas('participant_qr_credentials', ['id' => $active->id, 'status' => 'REVOKED']);
        $this->assertDatabaseHas('audit_logs', ['event' => 'participant_qr.revoked']);
    }

    public function test_scanner_page_includes_camera_controls_and_manual_fallback(): void
    {
        [$hotel] = $this->meetingFixture();
        $user = $this->scannerUser($hotel);

        $this->actingAs($user)->get(route('scanner.index'))->assertRedirect(route('redirect'))->assertSessionHas('Redirect', 'scanner');
        $this->followingRedirects()->actingAs($user)->get(route('scanner.index'))
            ->assertOk()
            ->assertSee('QR Scanner')
            ->assertDontSee('403');

        $this->actingAs($user)->withHeader('X-Requested-With', 'XMLHttpRequest')->get(route('scanner.index'))
            ->assertOk()
            ->assertSee('Start camera')
            ->assertSee('Stop camera')
            ->assertSee('Participant Entitlement Scanner')
            ->assertSee('Participant QR Token or URL')
            ->assertSee('Scan Entitlement')
            ->assertSee('HeadCounterScanner.init');
    }

    public function test_scanner_page_auto_generates_open_sessions_for_package_backed_meetings(): void
    {
        [$hotel, $firstMeeting] = $this->meetingFixture();
        [, $secondMeeting] = $this->meetingFixtureForHotel($hotel, 'Second Scanner Meeting');
        $user = $this->scannerUser($hotel);

        MealSession::withoutGlobalScope('hotel')->whereIn('meeting_event_id', [$firstMeeting->id, $secondMeeting->id])->delete();

        $this->actingAs($user)->withHeader('X-Requested-With', 'XMLHttpRequest')->get(route('scanner.index'))
            ->assertOk()
            ->assertSee('Phase Four Meeting')
            ->assertSee('Second Scanner Meeting');

        $this->assertSame(1, MealSession::withoutGlobalScope('hotel')->where('meeting_event_id', $firstMeeting->id)->where('status', MealSessionStatus::OPEN->value)->count());
        $this->assertSame(1, MealSession::withoutGlobalScope('hotel')->where('meeting_event_id', $secondMeeting->id)->where('status', MealSessionStatus::OPEN->value)->count());
    }

    public function test_phase_four_postgresql_partial_unique_index_exists(): void
    {
        $indexes = DB::table('pg_indexes')->where('indexname', 'redemptions_one_active_success')->value('indexdef');

        $this->assertStringContainsString('status', $indexes);
        $this->assertStringContainsString('SUCCESS', $indexes);
        $this->assertStringContainsString('OVERRIDDEN', $indexes);
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
            'full_name' => 'Redeem Guest',
            'email' => 'redeem.guest@example.test',
            'phone' => '+6281299999999',
        ]);

        return [$hotel, $meeting, $session, $result['participant_qr_token'], $this->scannerUser($hotel)];
    }

    private function meetingFixture(): array
    {
        $hotel = Hotel::create(['code' => 'H'.uniqid(), 'name' => 'Phase Four Hotel']);

        return $this->meetingFixtureForHotel($hotel, 'Phase Four Meeting');
    }

    private function meetingFixtureForHotel(Hotel $hotel, string $meetingName): array
    {
        $client = Client::create(['hotel_id' => $hotel->id, 'company_name' => 'Phase Four Client']);
        $booking = Booking::create(['hotel_id' => $hotel->id, 'client_id' => $client->id, 'booking_number' => 'BKG-'.uniqid(), 'booking_source' => 'TEST', 'booking_date' => '2026-07-01', 'status' => 'CONFIRMED']);
        $room = MeetingRoom::create(['hotel_id' => $hotel->id, 'code' => 'ROOM-'.uniqid(), 'name' => 'Phase Four Room', 'operational_status' => RoomOperationalStatus::AVAILABLE]);
        $meeting = MeetingEvent::create([
            'hotel_id' => $hotel->id,
            'booking_id' => $booking->id,
            'meeting_room_id' => $room->id,
            'event_name' => $meetingName,
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
