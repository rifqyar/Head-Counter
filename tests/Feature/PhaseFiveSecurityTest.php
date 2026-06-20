<?php

namespace Tests\Feature;

use App\Domain\Audit\AuditLog;
use App\Domain\Hotel\Hotel;
use App\Domain\Integration\IntegrationApiKey;
use App\Models\User;
use App\Services\IntegrationApiKeyService;
use App\Support\Audit\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PhaseFiveSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_permission_seeder_is_idempotent_and_builds_phase_five_matrix(): void
    {
        $this->seed();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        foreach (\Database\Seeders\RolePermissionSeeder::ROLES as $role) {
            $this->assertDatabaseHas('roles', ['name' => $role, 'guard_name' => 'web']);
        }

        foreach (\Database\Seeders\RolePermissionSeeder::PERMISSIONS as $permission) {
            $this->assertDatabaseHas('permissions', ['name' => $permission, 'guard_name' => 'web']);
        }

        $this->assertTrue(Role::findByName('SUPER_ADMIN')->hasPermissionTo('integration.manage'));
        $this->assertTrue(Role::findByName('SCANNER_OPERATOR')->hasPermissionTo('redemption.scan'));
        $this->assertFalse(Role::findByName('SCANNER_OPERATOR')->hasPermissionTo('redemption.override'));
        $this->assertSame(1, Role::where('name', 'SUPER_ADMIN')->count());
    }

    public function test_security_headers_are_applied_without_hsts_outside_production(): void
    {
        $this->get('/login')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Permissions-Policy', 'camera=(self), microphone=(), geolocation=()')
            ->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_audit_logger_masks_sensitive_fields(): void
    {
        $this->seed();

        app(AuditLogger::class)->record('security.test', null, null, null, [
            'password' => 'secret',
            'qr_token' => 'raw-token',
            'safe' => 'visible',
        ]);

        $metadata = DB::table('audit_logs')->where('action', 'security.test')->value('metadata');

        $this->assertStringContainsString('[REDACTED]', $metadata);
        $this->assertStringContainsString('visible', $metadata);
        $this->assertStringNotContainsString('raw-token', $metadata);
        $this->assertStringNotContainsString('secret', $metadata);
    }

    public function test_audit_log_ui_is_read_only_and_tenant_scoped(): void
    {
        $this->seed();
        [$hotelA, $userA] = $this->hotelUser('ORIA');
        [$hotelB] = $this->hotelUser('AONE-WH');

        $own = AuditLog::withoutGlobalScope('hotel')->create([
            'hotel_id' => $hotelA->id,
            'action' => 'own.action',
            'event' => 'own.action',
            'metadata' => [],
        ]);
        $foreign = AuditLog::withoutGlobalScope('hotel')->create([
            'hotel_id' => $hotelB->id,
            'action' => 'foreign.action',
            'event' => 'foreign.action',
            'metadata' => [],
        ]);

        $this->actingAs($userA)->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('audit-logs.index'))
            ->assertOk()
            ->assertSee('own.action')
            ->assertDontSee('foreign.action');

        $this->actingAs($userA)->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('audit-logs.show', $own))
            ->assertOk();

        $this->actingAs($userA)->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('audit-logs.show', $foreign))
            ->assertNotFound();

        $this->actingAs($userA)->delete('/audit-logs/'.$own->id)->assertStatus(405);
    }

    public function test_scanner_api_requires_authenticated_user_with_permission(): void
    {
        $this->seed();
        [$hotel] = $this->hotelUser('ORIA');
        $user = User::create([
            'hotel_id' => $hotel->id,
            'name' => 'No Scanner Permission',
            'username' => 'noscanner',
            'email' => 'noscanner@example.test',
            'password' => 'password',
        ]);

        $this->postJson('/api/v1/scanner/validate', [])->assertUnauthorized();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/scanner/validate', [
                'qr_token' => 'invalid',
                'meal_session_id' => 999999,
                'device_id' => 'test-device',
            ])
            ->assertForbidden();

        $scanner = User::create([
            'hotel_id' => $hotel->id,
            'name' => 'Phase Five Scanner',
            'username' => 'phase5scanner',
            'email' => 'phase5scanner@example.test',
            'password' => 'password',
        ]);
        $scanner->givePermissionTo('redemption.scan');

        $this->actingAs($scanner, 'sanctum')
            ->postJson('/api/v1/scanner/validate', [
                'qr_token' => 'invalid',
                'meal_session_id' => 999999,
                'device_id' => 'test-device',
            ])
            ->assertStatus(422);
    }

    public function test_integration_api_key_secret_is_shown_once_hashed_and_ability_scoped(): void
    {
        $this->seed();
        [$hotel, $user] = $this->hotelUser('ORIA');
        $service = app(IntegrationApiKeyService::class);

        $issued = $service->create($hotel->id, 'PMS Test', ['booking.read'], $user->id);
        $key = $issued['key']->refresh();

        $this->assertNotNull($issued['secret']);
        $this->assertNotSame($issued['secret'], $key->secret_hash);
        $this->assertArrayNotHasKey('secret', $key->toArray());
        $this->assertInstanceOf(IntegrationApiKey::class, $service->validate($issued['secret'], 'booking.read', $hotel->id));
        $this->assertNull($service->validate($issued['secret'], 'booking.write', $hotel->id));

        $service->revoke($key, $user->id);
        $this->assertNull($service->validate($issued['secret'], 'booking.read', $hotel->id));
        $this->assertDatabaseHas('audit_logs', ['action' => 'integration.api_key.revoked', 'entity_id' => $key->id]);
    }

    public function test_login_is_throttled_and_failed_attempts_are_audited(): void
    {
        $this->seed();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'username' => 'superadmin',
                'password' => 'wrong-password',
            ])->assertSessionHasErrors('username');
        }

        $this->post('/login', [
            'username' => 'superadmin',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('username');

        $this->assertDatabaseHas('audit_logs', ['action' => 'auth.login.failed']);
    }

    private function hotelUser(string $hotelCode): array
    {
        $hotel = Hotel::where('code', $hotelCode)->firstOrFail();
        $user = User::where('hotel_id', $hotel->id)->where('username', strtolower(str_replace('-', '', $hotelCode)).'.admin')->firstOrFail();

        Permission::findOrCreate('audit.view', 'web');
        Permission::findOrCreate('redemption.scan', 'web');
        $user->givePermissionTo(['audit.view', 'redemption.scan']);

        return [$hotel, $user];
    }
}
