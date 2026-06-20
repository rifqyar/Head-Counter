<?php

namespace App\Actions;

use App\Domain\Redemption\ParticipantEntitlement;
use App\Domain\Redemption\Redemption;
use App\Enums\RedemptionStatus;
use App\Support\Audit\AuditLogger;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OverrideRedemptionAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function execute(Redemption $redemption, int $actorId, string $reason): Redemption
    {
        return DB::transaction(function () use ($redemption, $actorId, $reason) {
            $locked = Redemption::withoutGlobalScope('hotel')->whereKey($redemption->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== RedemptionStatus::REJECTED) {
                $this->auditLogger->record('redemption.override_rejected', $locked->hotel_id, $actorId, $locked, ['reason' => 'not_rejected']);
                throw new \RuntimeException('Only rejected redemptions may be overridden.');
            }

            $code = $locked->rejection_code?->value ?? $locked->rejection_code;
            if (! in_array($code, RedeemParticipantAction::OVERRIDEABLE_REJECTION_CODES, true)) {
                $this->auditLogger->record('redemption.override_rejected', $locked->hotel_id, $actorId, $locked, ['reason' => 'code_not_overrideable', 'rejection_code' => $code]);
                throw new \RuntimeException('This rejection cannot be overridden.');
            }

            $alreadyRedeemed = Redemption::withoutGlobalScope('hotel')
                ->where('participant_id', $locked->participant_id)
                ->where('meal_session_id', $locked->meal_session_id)
                ->whereIn('status', [RedemptionStatus::SUCCESS->value, RedemptionStatus::OVERRIDDEN->value])
                ->exists();

            if ($alreadyRedeemed) {
                $this->auditLogger->record('redemption.override_rejected', $locked->hotel_id, $actorId, $locked, ['reason' => 'already_redeemed']);
                throw new \RuntimeException('An active successful redemption already exists.');
            }

            $entitlement = $locked->participant_entitlement_id
                ? ParticipantEntitlement::whereKey($locked->participant_entitlement_id)->lockForUpdate()->first()
                : ParticipantEntitlement::where('participant_id', $locked->participant_id)
                    ->where('meeting_event_id', $locked->meeting_event_id)
                    ->where('entitlement_type', $locked->mealSession?->entitlement_type->value ?? $locked->mealSession?->entitlement_type)
                    ->lockForUpdate()
                    ->first();

            if (! $entitlement || $entitlement->remaining_quantity < 1) {
                $this->auditLogger->record('redemption.override_rejected', $locked->hotel_id, $actorId, $locked, ['reason' => 'no_remaining_entitlement']);
                throw new \RuntimeException('No remaining entitlement is available for override.');
            }

            try {
                $override = Redemption::create([
                    'hotel_id' => $locked->hotel_id,
                    'participant_id' => $locked->participant_id,
                    'meeting_event_id' => $locked->meeting_event_id,
                    'meal_session_id' => $locked->meal_session_id,
                    'participant_entitlement_id' => $entitlement->id,
                    'original_redemption_id' => $locked->id,
                    'redemption_number' => 'OVR-'.now()->format('YmdHis').'-'.Str::upper(Str::random(8)),
                    'redeemed_at' => now(),
                    'scanned_by' => $actorId,
                    'device_id' => $locked->device_id,
                    'idempotency_key' => $locked->idempotency_key ? $locked->idempotency_key.'-override' : null,
                    'status' => RedemptionStatus::OVERRIDDEN,
                    'override_reason' => $reason,
                    'metadata' => [
                        'original_redemption_id' => $locked->id,
                        'overridden_by' => $actorId,
                        'overridden_at' => now()->toIso8601String(),
                        'original_rejection_code' => $code,
                    ],
                    'created_at' => now(),
                ]);
            } catch (QueryException $exception) {
                if (($exception->errorInfo[0] ?? null) === '23505') {
                    $this->auditLogger->record('redemption.override_concurrent_blocked', $locked->hotel_id, $actorId, $locked, ['rejection_code' => $code]);
                    throw new \RuntimeException('Another override or redemption already completed.');
                }

                throw $exception;
            }

            $entitlement->update([
                'redeemed_quantity' => $entitlement->redeemed_quantity + 1,
                'remaining_quantity' => $entitlement->remaining_quantity - 1,
            ]);

            $this->auditLogger->record('redemption.override_succeeded', $locked->hotel_id, $actorId, $override, ['reason' => $reason, 'rejection_code' => $code, 'original_redemption_id' => $locked->id]);

            return $override->fresh();
        });
    }
}
