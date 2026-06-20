<?php

namespace App\Domain\Redemption;

use App\Domain\Meeting\MeetingEvent;
use App\Domain\Participant\Participant;
use App\Support\Audit\AuditLogger;
use Illuminate\Support\Collection;

class ParticipantEntitlementService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function generateForParticipant(Participant $participant, ?int $actorId = null): Collection
    {
        $totals = $this->calculateTotals($participant->meetingEvent);
        $rows = collect();

        foreach ($totals as $type => $quantity) {
            $row = ParticipantEntitlement::updateOrCreate(
                [
                    'participant_id' => $participant->id,
                    'meeting_event_id' => $participant->meeting_event_id,
                    'entitlement_type' => $type,
                ],
                [
                    'total_quantity' => $quantity,
                    'redeemed_quantity' => 0,
                    'remaining_quantity' => $quantity,
                ]
            );
            $rows->push($row);
        }

        $this->auditLogger->record('participant_entitlements.generated', $participant->hotel_id, $actorId, $participant, ['totals' => $totals]);

        return $rows;
    }

    public function calculateTotals(MeetingEvent $meeting): array
    {
        $meeting->loadMissing('packageAssignments.package.entitlements');
        $totals = [];

        foreach ($meeting->packageAssignments as $assignment) {
            foreach ($assignment->package?->entitlements ?? [] as $entitlement) {
                $type = $entitlement->entitlement_type->value ?? $entitlement->entitlement_type;
                $totals[$type] = ($totals[$type] ?? 0) + (int) $entitlement->quantity;
            }
        }

        return $totals;
    }
}
