<?php

namespace App\Services;

use App\Domain\Meeting\MeetingEvent;
use App\Domain\Meeting\MeetingRoom;
use App\Enums\MeetingStatus;
use App\Enums\RoomOperationalStatus;
use App\Exceptions\DomainException;
use Illuminate\Support\Facades\DB;

class MeetingStateTransition
{
    public const ALLOWED = [
        'DRAFT' => ['SCHEDULED', 'CANCELLED'],
        'SCHEDULED' => ['CHECKIN_OPEN', 'OCCUPIED', 'COMPLETED', 'CANCELLED', 'NO_SHOW'],
        'CHECKIN_OPEN' => ['OCCUPIED', 'COMPLETED', 'CANCELLED', 'NO_SHOW'],
        'OCCUPIED' => ['COMPLETED', 'CANCELLED'],
        'COMPLETED' => [],
        'CANCELLED' => [],
        'NO_SHOW' => [],
    ];

    public function transition(MeetingEvent $meetingEvent, MeetingStatus $target): MeetingEvent
    {
        $current = $meetingEvent->status instanceof MeetingStatus
            ? $meetingEvent->status
            : MeetingStatus::from($meetingEvent->status);

        if (! in_array($target->value, self::ALLOWED[$current->value] ?? [], true)) {
            throw new DomainException("Invalid meeting status transition from {$current->value} to {$target->value}.");
        }

        return DB::transaction(function () use ($meetingEvent, $target) {
            $updates = ['status' => $target->value];

            if ($target === MeetingStatus::CHECKIN_OPEN) {
                $updates['checkin_open_at'] = now();
            }

            if ($target === MeetingStatus::OCCUPIED) {
                $updates['started_at'] = now();
            }

            if ($target === MeetingStatus::COMPLETED) {
                $updates['checkin_open_at'] = $meetingEvent->checkin_open_at ?: now();
                $updates['started_at'] = $meetingEvent->started_at ?: now();
                $updates['completed_at'] = now();
            }

            if ($target === MeetingStatus::CANCELLED) {
                $updates['cancelled_at'] = now();
            }

            $meetingEvent->update($updates);
            $this->syncRoomStatus($meetingEvent->fresh(), $target);

            return $meetingEvent->fresh();
        });
    }

    public function syncRoomStatus(MeetingEvent $meetingEvent, MeetingStatus $status): void
    {
        if (! $meetingEvent->meeting_room_id) {
            return;
        }

        $room = MeetingRoom::withoutGlobalScope('hotel')->find($meetingEvent->meeting_room_id);

        if (! $room) {
            return;
        }

        $roomStatus = match ($status) {
            MeetingStatus::SCHEDULED, MeetingStatus::CHECKIN_OPEN => RoomOperationalStatus::RESERVED,
            MeetingStatus::OCCUPIED => RoomOperationalStatus::OCCUPIED,
            MeetingStatus::COMPLETED => RoomOperationalStatus::CLEANING,
            MeetingStatus::CANCELLED, MeetingStatus::NO_SHOW => $this->recalculateRoomStatus($meetingEvent),
            default => null,
        };

        if ($roomStatus !== null) {
            $room->update(['operational_status' => $roomStatus->value]);
        }
    }

    private function recalculateRoomStatus(MeetingEvent $meetingEvent): RoomOperationalStatus
    {
        $activeMeetings = MeetingEvent::withoutGlobalScope('hotel')
            ->where('hotel_id', $meetingEvent->hotel_id)
            ->where('meeting_room_id', $meetingEvent->meeting_room_id)
            ->whereKeyNot($meetingEvent->id)
            ->whereIn('status', [
                MeetingStatus::SCHEDULED->value,
                MeetingStatus::CHECKIN_OPEN->value,
                MeetingStatus::OCCUPIED->value,
            ])
            ->get();

        if ($activeMeetings->contains(fn (MeetingEvent $event) => $event->status === MeetingStatus::OCCUPIED)) {
            return RoomOperationalStatus::OCCUPIED;
        }

        if ($activeMeetings->isNotEmpty()) {
            return RoomOperationalStatus::RESERVED;
        }

        return RoomOperationalStatus::AVAILABLE;
    }
}
