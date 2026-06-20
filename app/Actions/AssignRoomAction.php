<?php

namespace App\Actions;

use App\Domain\Meeting\MeetingEvent;
use App\Enums\RoomOperationalStatus;
use App\Exceptions\DomainException;
use App\Services\MeetingRoomConflictService;
use Illuminate\Support\Facades\DB;

class AssignRoomAction
{
    public function __construct(private readonly MeetingRoomConflictService $conflicts) {}

    public function execute(MeetingEvent $meetingEvent, int $meetingRoomId): MeetingEvent
    {
        $conflict = $this->conflicts->findConflict(
            $meetingEvent->hotel_id,
            $meetingRoomId,
            $meetingEvent->start_at,
            $meetingEvent->end_at,
            $meetingEvent->id,
        );

        if ($conflict) {
            throw new DomainException("Room is already assigned to {$conflict->event_name}.");
        }

        return DB::transaction(function () use ($meetingEvent, $meetingRoomId) {
            $meetingEvent->update(['meeting_room_id' => $meetingRoomId]);
            $meetingEvent->meetingRoom()->withoutGlobalScopes()->update(['operational_status' => RoomOperationalStatus::RESERVED->value]);

            return $meetingEvent->fresh();
        });
    }
}
