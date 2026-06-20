<?php

namespace App\Support\Audit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AuditLogger
{
    public function record(string $event, ?int $hotelId = null, ?int $actorId = null, ?Model $auditable = null, array $metadata = []): void
    {
        DB::table('audit_logs')->insert([
            'hotel_id' => $hotelId,
            'actor_id' => $actorId,
            'event' => $event,
            'auditable_type' => $auditable ? $auditable::class : null,
            'auditable_id' => $auditable?->getKey(),
            'metadata' => json_encode($metadata),
            'created_at' => now(),
        ]);
    }
}
