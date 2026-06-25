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
use App\Enums\MealSessionStatus;
use App\Enums\MeetingStatus;
use App\Enums\RoomOperationalStatus;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PhaseFiveCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_mutations_are_audited_with_before_and_after_data(): void
    {
        $this->seed();
        [$hotel, $user] = $this->hotelUser('ORIA');
        $client = Client::create(['hotel_id' => $hotel->id, 'company_name' => 'Phase Five Booking Client']);
        $client->hotels()->syncWithoutDetaching([$hotel->id => ['status' => 'ACTIVE', 'metadata' => json_encode(['source' => 'test'])]]);

        $this->actingAs($user)->post('/bookings', [
            'client_id' => $client->id,
            'booking_number' => 'P5-BKG-001',
            'booking_source' => 'DIRECT',
            'booking_date' => '2026-09-01',
            'status' => 'CONFIRMED',
        ])->assertRedirect();

        $booking = Booking::withoutGlobalScope('hotel')->where('booking_number', 'P5-BKG-001')->firstOrFail();
        $this->assertDatabaseHas('audit_logs', ['action' => 'booking.created', 'entity_id' => $booking->id]);

        $this->actingAs($user)->put(route('bookings.update', $booking), [
            'client_id' => $client->id,
            'booking_number' => 'P5-BKG-001',
            'booking_source' => 'DIRECT',
            'booking_date' => '2026-09-02',
            'status' => 'CONFIRMED',
        ])->assertRedirect();
        $this->assertDatabaseHas('audit_logs', ['action' => 'booking.updated', 'entity_id' => $booking->id]);

        $this->actingAs($user)->delete(route('bookings.destroy', $booking))->assertRedirect();
        $this->assertDatabaseHas('audit_logs', ['action' => 'booking.cancelled', 'entity_id' => $booking->id]);
    }

    public function test_tenant_scoped_user_management_blocks_super_admin_assignment_and_revokes_tokens_on_deactivation(): void
    {
        $this->seed();
        [$hotel, $hotelAdmin] = $this->hotelUser('ORIA');

        $gm = User::where('hotel_id', $hotel->id)->where('username', 'oria.gm')->firstOrFail();

        foreach ([$hotelAdmin, $gm] as $manager) {
            $this->assertTrue($manager->can('Setting'));
            $this->assertTrue($manager->can('Manage User'));
            $this->assertTrue($manager->can('settings.manage'));
            $this->assertTrue($manager->can('user.manage'));

            $this->followingRedirects()
                ->actingAs($manager)
                ->get(route('dashboard'))
                ->assertOk()
                ->assertSee('Setting')
                ->assertSee('Manage User')
                ->assertSee('Hotel Settings');
        }

        $this->actingAs($hotelAdmin)->post(route('users.store'), [
            'name' => 'Hotel Staff',
            'username' => 'hotel.staff',
            'email' => 'hotel.staff@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'status' => 'ACTIVE',
            'roles' => ['SUPER_ADMIN'],
        ])->assertSessionHasErrors('roles.0');

        $this->actingAs($hotelAdmin)->post(route('users.store'), [
            'name' => 'Hotel Scanner',
            'username' => 'hotel.scanner',
            'email' => 'hotel.scanner@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'status' => 'ACTIVE',
            'roles' => ['SCANNER_OPERATOR'],
        ])->assertRedirect();

        $managed = User::where('username', 'hotel.scanner')->firstOrFail();
        $this->assertSame($hotel->id, $managed->hotel_id);
        $token = $managed->createToken('scanner', ['scanner:validate'])->accessToken;

        $this->actingAs($hotelAdmin)->post(route('users.deactivate', $managed))->assertRedirect();
        $this->assertSame('INACTIVE', $managed->refresh()->status);
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.deactivated', 'entity_id' => $managed->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.tokens_revoked', 'entity_id' => $managed->id]);
    }

    public function test_hotel_manager_can_complete_scheduled_meeting(): void
    {
        $this->seed();
        [$hotel, $hotelAdmin] = $this->hotelUser('ORIA');
        $meeting = MeetingEvent::where('hotel_id', $hotel->id)->where('status', MeetingStatus::SCHEDULED)->firstOrFail();

        $this->actingAs($hotelAdmin)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('meetings.show', $meeting))
            ->assertOk()
            ->assertSee('COMPLETED');

        $this->actingAs($hotelAdmin)
            ->post(route('meetings.transition', $meeting), ['status' => 'COMPLETED'])
            ->assertRedirect(route('meetings.show', $meeting));

        $meeting->refresh();
        $this->assertSame(MeetingStatus::COMPLETED, $meeting->status);
        $this->assertNotNull($meeting->started_at);
        $this->assertNotNull($meeting->completed_at);
    }

    public function test_last_super_admin_is_protected(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($superAdmin)->post(route('users.deactivate', $superAdmin))->assertStatus(422);
        $this->assertTrue($superAdmin->refresh()->isActive());
    }

    public function test_tenant_admin_can_update_own_hotel_settings(): void
    {
        $this->seed();
        [$hotel, $hotelAdmin] = $this->hotelUser('ORIA');

        $this->actingAs($hotelAdmin)->put(route('settings.update'), [
            'name' => 'Oria Hotel Jakarta Operations',
            'address' => 'Jl. K.H. Wahid Hasyim',
            'timezone' => 'Asia/Jakarta',
            'settings' => [
                'logo_path' => 'images/oria-logo.png',
                'contact_email' => 'ops.oria@example.test',
                'contact_phone' => '+622100000',
                'meeting_qr_note' => 'Bring your invitation QR.',
                'default_booking_source' => 'DIRECT',
            ],
        ])->assertRedirect(route('settings.index'));

        $hotel->refresh();
        $this->assertSame('Oria Hotel Jakarta Operations', $hotel->name);
        $this->assertSame('images/oria-logo.png', $hotel->settings['logo_path']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'settings.updated', 'hotel_id' => $hotel->id]);
    }

    public function test_super_admin_can_manage_hotel_subscription_and_filter_tenant_users(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();
        $hotel = Hotel::where('code', 'ORIA')->firstOrFail();

        $this->actingAs($superAdmin)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('settings.subscriptions.index'))
            ->assertOk()
            ->assertSee('Subscriptions')
            ->assertSee('Hotel')
            ->assertSee('Status');

        $this->actingAs($superAdmin)->put(route('settings.subscriptions.update', $hotel), [
            'plan' => 'Enterprise Pilot',
            'status' => 'ACTIVE',
            'started_at' => '2026-06-01',
            'expires_at' => '2027-06-01',
            'max_users' => 50,
            'notes' => 'Annual subscription',
        ])->assertRedirect(route('settings.subscriptions.index'));

        $hotel->refresh();
        $this->assertSame('Enterprise Pilot', $hotel->settings['subscription']['plan']);
        $this->assertSame('ACTIVE', $hotel->settings['subscription']['status']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'hotel.subscription_updated', 'hotel_id' => $hotel->id]);

        $this->actingAs($superAdmin)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('users.index', ['hotel_id' => $hotel->id]))
            ->assertOk()
            ->assertSee('Tenant')
            ->assertSee('oria.admin')
            ->assertDontSee('aonewh.admin');
    }

    public function test_role_and_permission_mutations_are_audited_and_protected(): void
    {
        $this->seed();
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();
        $role = Role::findByName('SCANNER_OPERATOR');

        $this->actingAs($superAdmin)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('setting.role'))
            ->assertOk()
            ->assertSee('Manage Roles')
            ->assertSee('Create Role');

        $this->actingAs($superAdmin)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('setting.permission'))
            ->assertOk()
            ->assertSee('Manage Permissions')
            ->assertSee('Create Permission');

        $this->actingAs($superAdmin)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('role.data'), ['draw' => 1, 'start' => 0, 'length' => 10])
            ->assertOk()
            ->assertJson(fn ($json) => $json->has('data.0.name')->etc());

        $this->actingAs($superAdmin)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('permission.data'), ['draw' => 1, 'start' => 0, 'length' => 10])
            ->assertOk()
            ->assertJson(fn ($json) => $json->has('data.0.name')->etc());

        $this->actingAs($superAdmin)->withHeader('X-Requested-With', 'XMLHttpRequest')->post(route('role.store'), ['name' => 'CUSTOM_SECURITY_ROLE'])->assertOk();
        $this->assertDatabaseHas('audit_logs', ['action' => 'role.created']);

        $permission = Permission::findByName('redemption.scan');
        $this->actingAs($superAdmin)->withHeader('X-Requested-With', 'XMLHttpRequest')->post(route('role.store-permission'), [
            'role_id' => $role->id,
            'permissions' => [$permission->name],
        ])->assertOk();
        $this->assertDatabaseHas('audit_logs', ['action' => 'role.permissions_synced', 'entity_id' => $role->id]);

        $this->actingAs($superAdmin)->withHeader('X-Requested-With', 'XMLHttpRequest')->post(route('permission.store'), ['name' => 'custom.security.permission'])->assertOk();
        $created = Permission::where('name', 'custom.security.permission')->firstOrFail();
        $this->assertDatabaseHas('audit_logs', ['action' => 'permission.created', 'entity_id' => $created->id]);

        $this->actingAs($superAdmin)->withHeader('X-Requested-With', 'XMLHttpRequest')->get(route('permission.destroy', $permission->id))->assertStatus(422);
    }

    public function test_real_sanctum_scanner_tokens_require_ability_permission_active_user_and_active_hotel(): void
    {
        [$hotel, , $session, , $scanner] = $this->scannerFixture('TOKA');
        $missingAbility = $scanner->createToken('missing', ['meeting:read'])->plainTextToken;
        $this->withToken($missingAbility)
            ->postJson('/api/v1/scanner/validate', ['qr_token' => 'invalid', 'meal_session_id' => $session->id])
            ->assertForbidden();
        app('auth')->forgetGuards();

        $valid = $scanner->createToken('valid', ['scanner:validate'])->plainTextToken;
        $scanner->refresh();
        $this->flushHeaders()
            ->withToken($valid)
            ->postJson('/api/v1/scanner/validate', ['qr_token' => 'invalid', 'meal_session_id' => $session->id])
            ->assertStatus(422);
        app('auth')->forgetGuards();

        $scanner->tokens()->delete();
        $this->flushHeaders()
            ->withToken($valid)
            ->postJson('/api/v1/scanner/validate', ['qr_token' => 'invalid', 'meal_session_id' => $session->id])
            ->assertUnauthorized();
        app('auth')->forgetGuards();

        $inactiveToken = $scanner->createToken('inactive-user', ['scanner:validate'])->plainTextToken;
        $scanner->forceFill(['status' => 'INACTIVE', 'deactivated_at' => now()])->save();
        $this->flushHeaders()
            ->withToken($inactiveToken)
            ->postJson('/api/v1/scanner/validate', ['qr_token' => 'invalid', 'meal_session_id' => $session->id])
            ->assertForbidden();
        app('auth')->forgetGuards();

        $scanner->forceFill(['status' => 'ACTIVE', 'deactivated_at' => null])->save();
        $inactiveHotelToken = $scanner->createToken('inactive-hotel', ['scanner:validate'])->plainTextToken;
        $hotel->update(['status' => 'INACTIVE']);
        $this->flushHeaders()
            ->withToken($inactiveHotelToken)
            ->postJson('/api/v1/scanner/validate', ['qr_token' => 'invalid', 'meal_session_id' => $session->id])
            ->assertForbidden();
    }

    public function test_cross_hotel_scanner_token_cannot_validate_foreign_qr_or_session(): void
    {
        [, , $sessionA, , $scannerA] = $this->scannerFixture('TOKB');
        [, , $sessionB, $tokenB] = $this->scannerFixture('TOKC');
        $token = $scannerA->createToken('scanner-a', ['scanner:validate'])->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/scanner/validate', [
                'qr_token' => $tokenB,
                'meal_session_id' => $sessionB->id,
                'device_id' => 'scanner-a',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('meal_session_id');

        $this->withToken($token)
            ->postJson('/api/v1/scanner/validate', [
                'qr_token' => $tokenB,
                'meal_session_id' => $sessionA->id,
                'device_id' => 'scanner-a',
            ])
            ->assertStatus(422)
            ->assertJsonPath('eligible', false);
    }

    private function hotelUser(string $hotelCode): array
    {
        $hotel = Hotel::where('code', $hotelCode)->firstOrFail();
        $user = User::where('hotel_id', $hotel->id)->where('username', strtolower(str_replace('-', '', $hotelCode)).'.admin')->firstOrFail();

        return [$hotel, $user];
    }

    private function scannerFixture(string $prefix): array
    {
        $hotel = Hotel::create(['code' => $prefix, 'name' => $prefix.' Hotel']);
        $client = Client::create(['hotel_id' => $hotel->id, 'company_name' => $prefix.' Client']);
        $booking = Booking::create(['hotel_id' => $hotel->id, 'client_id' => $client->id, 'booking_number' => $prefix.'-BKG', 'booking_source' => 'TEST', 'booking_date' => '2026-09-01', 'status' => 'CONFIRMED']);
        $room = MeetingRoom::create(['hotel_id' => $hotel->id, 'code' => $prefix.'-ROOM', 'name' => $prefix.' Room', 'operational_status' => RoomOperationalStatus::AVAILABLE]);
        $meeting = MeetingEvent::create([
            'hotel_id' => $hotel->id,
            'booking_id' => $booking->id,
            'meeting_room_id' => $room->id,
            'event_name' => $prefix.' Meeting',
            'event_date' => '2026-09-01',
            'start_at' => now()->subHour(),
            'end_at' => now()->addHours(2),
            'expected_participants' => 10,
            'status' => MeetingStatus::SCHEDULED,
            'checkin_open_at' => now()->subHour(),
            'checkin_close_at' => now()->addHour(),
        ]);
        $package = MeetingPackage::create(['hotel_id' => $hotel->id, 'code' => $prefix.'-PCK', 'name' => 'Package', 'price' => 100000]);
        PackageEntitlement::create(['package_id' => $package->id, 'entitlement_type' => 'COFFEE_BREAK', 'quantity' => 1]);
        MeetingPackageAssignment::create(['meeting_event_id' => $meeting->id, 'package_id' => $package->id, 'participant_quota' => 10, 'unit_price' => 100000]);
        $session = MealSession::create(['hotel_id' => $hotel->id, 'meeting_event_id' => $meeting->id, 'entitlement_type' => 'COFFEE_BREAK', 'session_number' => 1, 'name' => 'Coffee', 'status' => MealSessionStatus::OPEN]);

        $this->app->make(TenantContext::class)->set($hotel);
        $result = app(RegisterParticipantAction::class)->executeWithQr($meeting, [
            'full_name' => $prefix.' Guest',
            'email' => strtolower($prefix).'@example.test',
        ]);

        Permission::findOrCreate('redemption.scan', 'web');
        $scanner = User::create(['hotel_id' => $hotel->id, 'name' => $prefix.' Scanner', 'username' => strtolower($prefix).'scanner', 'email' => strtolower($prefix).'scanner@example.test', 'password' => 'password']);
        $scanner->givePermissionTo('redemption.scan');

        return [$hotel, $meeting, $session, $result['participant_qr_token'], $scanner];
    }
}
