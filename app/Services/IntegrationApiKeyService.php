<?php

namespace App\Services;

use App\Domain\Integration\IntegrationApiKey;
use App\Support\Audit\AuditLogger;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class IntegrationApiKeyService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function create(int $hotelId, string $name, array $abilities, ?int $actorId = null, mixed $expiresAt = null): array
    {
        $secret = Str::random(64);
        $prefix = 'hci_'.Str::lower(Str::random(8));

        $key = IntegrationApiKey::withoutGlobalScope('hotel')->create([
            'hotel_id' => $hotelId,
            'name' => $name,
            'key_prefix' => $prefix,
            'secret_hash' => Hash::make($secret),
            'abilities' => array_values($abilities),
            'expires_at' => $expiresAt,
            'created_by' => $actorId,
        ]);

        $this->auditLogger->record(
            'integration.api_key.created',
            $hotelId,
            $actorId,
            $key,
            ['key_prefix' => $prefix, 'abilities' => $abilities]
        );

        return ['key' => $key, 'secret' => $prefix.'.'.$secret];
    }

    public function validate(string $presentedSecret, ?string $requiredAbility = null, ?int $hotelId = null): ?IntegrationApiKey
    {
        [$prefix, $secret] = array_pad(explode('.', $presentedSecret, 2), 2, null);
        if (! $prefix || ! $secret) {
            return null;
        }

        $key = IntegrationApiKey::withoutGlobalScope('hotel')->where('key_prefix', $prefix)->first();
        if (! $key || ! $key->isActive() || ! Hash::check($secret, $key->secret_hash)) {
            return null;
        }

        if ($hotelId !== null && (int) $key->hotel_id !== (int) $hotelId) {
            return null;
        }

        $abilities = $key->abilities ?: [];
        if ($requiredAbility && ! in_array('*', $abilities, true) && ! in_array($requiredAbility, $abilities, true)) {
            return null;
        }

        $key->forceFill(['last_used_at' => now()])->save();

        return $key;
    }

    public function revoke(IntegrationApiKey $key, ?int $actorId = null): void
    {
        $key->forceFill([
            'status' => 'REVOKED',
            'revoked_at' => now(),
            'revoked_by' => $actorId,
        ])->save();

        $this->auditLogger->record(
            'integration.api_key.revoked',
            $key->hotel_id,
            $actorId,
            $key,
            ['key_prefix' => $key->key_prefix]
        );
    }
}
