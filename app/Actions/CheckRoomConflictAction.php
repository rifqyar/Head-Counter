<?php

namespace App\Actions;

use App\Services\MeetingRoomConflictService;

class CheckRoomConflictAction
{
    public function __construct(private readonly MeetingRoomConflictService $conflicts) {}

    public function execute(array $data): bool
    {
        return $this->conflicts->hasConflict(
            (int) $data['hotel_id'],
            (int) $data['meeting_room_id'],
            $data['start_at'],
            $data['end_at'],
            $data['exclude_meeting_event_id'] ?? null,
        );
    }
}
