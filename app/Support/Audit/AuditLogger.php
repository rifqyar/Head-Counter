<?php

namespace App\Support\Audit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuditLogger
{
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'remember_token',
        'token',
        'qr_token',
        'raw_token',
        'access_token',
        'refresh_token',
        'api_secret',
        'secret',
        'secret_hash',
        'authorization',
        'cookie',
        'session_id',
        'token_hash',
    ];

    public function record(string $event, ?int $hotelId = null, ?int $actorId = null, ?Model $auditable = null, array $metadata = [], array $before = [], array $after = []): void
    {
        $request = request();

        DB::table('audit_logs')->insert([
            'hotel_id' => $hotelId,
            'actor_type' => $actorId ? 'user' : null,
            'actor_id' => $actorId,
            'event' => $event,
            'action' => $event,
            'auditable_type' => $auditable ? $auditable::class : null,
            'auditable_id' => $auditable?->getKey(),
            'entity_type' => $auditable ? $auditable::class : null,
            'entity_id' => $auditable?->getKey(),
            'before_data' => json_encode($this->sanitize($before)),
            'after_data' => json_encode($this->sanitize($after)),
            'metadata' => json_encode($this->sanitize($metadata)),
            'ip_address' => app()->runningInConsole() ? null : $request?->ip(),
            'user_agent' => app()->runningInConsole() ? null : Str::limit((string) $request?->userAgent(), 1000, ''),
            'request_id' => app()->runningInConsole() || ! Str::isUuid((string) $request?->headers->get('X-Request-Id'))
                ? null
                : $request?->headers->get('X-Request-Id'),
            'created_at' => now(),
        ]);
    }

    public function sanitize(array $data): array
    {
        $clean = [];
        foreach ($data as $key => $value) {
            if ($this->isSensitive((string) $key)) {
                $clean[$key] = '[REDACTED]';

                continue;
            }

            $clean[$key] = is_array($value) ? $this->sanitize($value) : $value;
        }

        return $clean;
    }

    private function isSensitive(string $key): bool
    {
        $normalized = Str::lower($key);

        foreach (self::SENSITIVE_KEYS as $sensitive) {
            if (str_contains($normalized, $sensitive)) {
                return true;
            }
        }

        return false;
    }
}
