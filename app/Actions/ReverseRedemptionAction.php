<?php

namespace App\Actions;

use App\Domain\Redemption\ParticipantEntitlement;
use App\Domain\Redemption\Redemption;
use App\Enums\RedemptionStatus;
use App\Support\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;

class ReverseRedemptionAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(Redemption $redemption, int $actorId, string $reason): Redemption
    {
        return DB::transaction(function () use ($redemption, $actorId, $reason) {
            $locked = Redemption::withoutGlobalScope('hotel')->whereKey($redemption->id)->lockForUpdate()->firstOrFail();

            if (! in_array($locked->status, [RedemptionStatus::SUCCESS, RedemptionStatus::OVERRIDDEN], true)) {
                throw new \RuntimeException('Redemption is not reversible.');
            }

            $entitlement = ParticipantEntitlement::whereKey($locked->participant_entitlement_id)->lockForUpdate()->firstOrFail();
            $locked->update([
                'status' => RedemptionStatus::REVERSED,
                'override_reason' => $reason,
                'metadata' => array_merge($locked->metadata ?? [], ['reversed_by' => $actorId, 'reversed_at' => now()->toIso8601String()]),
            ]);
            $entitlement->update([
                'redeemed_quantity' => max(0, $entitlement->redeemed_quantity - 1),
                'remaining_quantity' => min($entitlement->total_quantity, $entitlement->remaining_quantity + 1),
            ]);

            $this->auditLogger->record('redemption.reversed', $locked->hotel_id, $actorId, $locked, ['reason' => $reason]);

            return $locked->fresh();
        });
    }
}
