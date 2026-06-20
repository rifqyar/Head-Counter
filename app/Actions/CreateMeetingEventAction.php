<?php

namespace App\Actions;

use App\Domain\Meeting\MeetingEvent;
use App\Enums\MeetingStatus;
use App\Exceptions\DomainException;
use App\Services\MeetingRoomConflictService;
use App\Services\MeetingStateTransition;
use Illuminate\Support\Facades\DB;

class CreateMeetingEventAction
{
    public function __construct(
        private readonly MeetingRoomConflictService $conflicts,
        private readonly MeetingStateTransition $stateTransition
    ) {}

    public function execute(array $data): MeetingEvent
    {
        if (! empty($data['meeting_room_id'])) {
            $conflict = $this->conflicts->findConflict($data['hotel_id'], $data['meeting_room_id'], $data['start_at'], $data['end_at']);

            if ($conflict) {
                throw new DomainException("Room is already assigned to {$conflict->event_name}.");
            }
        }

        return DB::transaction(function () use ($data) {
            $meeting = MeetingEvent::create($data);
            $status = $meeting->status instanceof MeetingStatus ? $meeting->status : MeetingStatus::from($meeting->status);
            $this->stateTransition->syncRoomStatus($meeting, $status);

            return $meeting;
        });
    }
}
