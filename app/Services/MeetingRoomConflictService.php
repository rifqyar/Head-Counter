<?php

namespace App\Services;

use App\Domain\Meeting\MeetingEvent;
use App\Enums\MeetingStatus;
use Carbon\CarbonInterface;

class MeetingRoomConflictService
{
    public function findConflict(
        int $hotelId,
        int $meetingRoomId,
        CarbonInterface|string $startAt,
        CarbonInterface|string $endAt,
        ?int $excludeMeetingEventId = null
    ): ?MeetingEvent {
        $query = MeetingEvent::withoutGlobalScope('hotel')
            ->where('hotel_id', $hotelId)
            ->where('meeting_room_id', $meetingRoomId)
            ->whereNotIn('status', [MeetingStatus::CANCELLED->value, MeetingStatus::NO_SHOW->value])
            ->where('start_at', '<', $endAt)
            ->where('end_at', '>', $startAt);

        if ($excludeMeetingEventId !== null) {
            $query->whereKeyNot($excludeMeetingEventId);
        }

        return $query->orderBy('start_at')->first();
    }

    public function hasConflict(int $hotelId, int $meetingRoomId, CarbonInterface|string $startAt, CarbonInterface|string $endAt, ?int $excludeMeetingEventId = null): bool
    {
        return $this->findConflict($hotelId, $meetingRoomId, $startAt, $endAt, $excludeMeetingEventId) !== null;
    }
}
