<?php

namespace App\Actions;

use App\Domain\Meeting\MeetingEvent;
use App\Domain\Redemption\ParticipantEntitlement;
use App\Domain\Redemption\ParticipantEntitlementService;
use App\Support\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;

class SynchronizeParticipantEntitlementsAction
{
    public function __construct(
        private readonly ParticipantEntitlementService $entitlementService,
        private readonly AuditLogger $auditLogger
    ) {}

    public function execute(MeetingEvent $meeting, ?int $actorId = null): array
    {
        return DB::transaction(function () use ($meeting, $actorId) {
            $totals = $this->entitlementService->calculateTotals($meeting);
            $report = ['updated' => 0, 'preserved_over_redeemed' => []];

            foreach ($meeting->participants()->withoutGlobalScope('hotel')->get() as $participant) {
                foreach ($totals as $type => $quantity) {
                    $row = ParticipantEntitlement::where('participant_id', $participant->id)
                        ->where('meeting_event_id', $meeting->id)
                        ->where('entitlement_type', $type)
                        ->lockForUpdate()
                        ->first();

                    if (! $row) {
                        ParticipantEntitlement::create([
                            'participant_id' => $participant->id,
                            'meeting_event_id' => $meeting->id,
                            'entitlement_type' => $type,
                            'total_quantity' => $quantity,
                            'redeemed_quantity' => 0,
                            'remaining_quantity' => $quantity,
                        ]);
                        $report['updated']++;

                        continue;
                    }

                    $newTotal = max($quantity, $row->redeemed_quantity);
                    if ($newTotal > $quantity) {
                        $report['preserved_over_redeemed'][] = $row->id;
                    }
                    $row->update([
                        'total_quantity' => $newTotal,
                        'remaining_quantity' => $newTotal - $row->redeemed_quantity,
                    ]);
                    $report['updated']++;
                }
            }

            $this->auditLogger->record('participant_entitlements.synchronized', $meeting->hotel_id, $actorId, $meeting, $report);

            return $report;
        });
    }
}
