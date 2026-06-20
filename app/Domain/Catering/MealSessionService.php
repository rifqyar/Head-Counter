<?php

namespace App\Domain\Catering;

use App\Domain\Meeting\MeetingEvent;
use App\Enums\MealSessionStatus;
use App\Support\Audit\AuditLogger;
use Illuminate\Support\Collection;

class MealSessionService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function generateFromPackages(MeetingEvent $meeting, ?int $actorId = null): Collection
    {
        $meeting->loadMissing('packageAssignments.package.entitlements');
        $created = collect();
        $sessionNumbers = [];

        foreach ($meeting->packageAssignments as $assignment) {
            foreach ($assignment->package?->entitlements ?? [] as $entitlement) {
                $type = $entitlement->entitlement_type->value ?? $entitlement->entitlement_type;
                for ($i = 1; $i <= (int) $entitlement->quantity; $i++) {
                    $sessionNumber = ($sessionNumbers[$type] ?? 0) + 1;
                    $sessionNumbers[$type] = $sessionNumber;
                    $created->push(MealSession::firstOrCreate(
                        [
                            'meeting_event_id' => $meeting->id,
                            'entitlement_type' => $type,
                            'session_number' => $sessionNumber,
                        ],
                        [
                            'hotel_id' => $meeting->hotel_id,
                            'name' => $this->defaultName($type, $sessionNumber),
                            'status' => MealSessionStatus::DRAFT,
                            'created_by' => $actorId,
                        ]
                    ));
                }
            }
        }

        $this->auditLogger->record('meal_sessions.generated', $meeting->hotel_id, $actorId, $meeting, ['count' => $created->count()]);

        return $created;
    }

    public function open(MealSession $session, ?int $actorId = null): MealSession
    {
        $session->update(['status' => MealSessionStatus::OPEN, 'updated_by' => $actorId]);
        $this->auditLogger->record('meal_session.opened', $session->hotel_id, $actorId, $session);

        return $session;
    }

    public function close(MealSession $session, ?int $actorId = null): MealSession
    {
        $session->update(['status' => MealSessionStatus::CLOSED, 'updated_by' => $actorId]);
        $this->auditLogger->record('meal_session.closed', $session->hotel_id, $actorId, $session);

        return $session;
    }

    public function cancel(MealSession $session, ?int $actorId = null): MealSession
    {
        $session->update(['status' => MealSessionStatus::CANCELLED, 'updated_by' => $actorId]);
        $this->auditLogger->record('meal_session.cancelled', $session->hotel_id, $actorId, $session);

        return $session;
    }

    private function defaultName(string $type, int $number): string
    {
        return match ($type) {
            'COFFEE_BREAK' => 'Coffee Break '.$number,
            'LUNCH' => 'Lunch',
            'DINNER' => 'Dinner',
            default => str($type)->replace('_', ' ')->title().' '.$number,
        };
    }
}
