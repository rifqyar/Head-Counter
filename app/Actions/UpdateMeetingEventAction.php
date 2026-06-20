<?php

namespace App\Actions;

use App\Domain\Meeting\MeetingEvent;
use App\Enums\MeetingStatus;
use App\Exceptions\DomainException;
use App\Services\MeetingRoomConflictService;
use App\Services\MeetingStateTransition;
use Illuminate\Support\Facades\DB;

class UpdateMeetingEventAction
{
    public function __construct(
        private readonly MeetingRoomConflictService $conflicts,
        private readonly MeetingStateTransition $stateTransition
    ) {}

    public function execute(MeetingEvent $meetingEvent, array $data): MeetingEvent
    {
        $roomId = $data['meeting_room_id'] ?? $meetingEvent->meeting_room_id;
        $startAt = $data['start_at'] ?? $meetingEvent->start_at;
        $endAt = $data['end_at'] ?? $meetingEvent->end_at;

        if ($roomId) {
            $conflict = $this->conflicts->findConflict($meetingEvent->hotel_id, $roomId, $startAt, $endAt, $meetingEvent->id);

            if ($conflict) {
                throw new DomainException("Room is already assigned to {$conflict->event_name}.");
            }
        }

        return DB::transaction(function () use ($meetingEvent, $data) {
            $meetingEvent->update($data);
            $fresh = $meetingEvent->fresh();
            $status = $fresh->status instanceof MeetingStatus ? $fresh->status : MeetingStatus::from($fresh->status);
            $this->stateTransition->syncRoomStatus($fresh, $status);

            return $fresh;
        });
    }
}
