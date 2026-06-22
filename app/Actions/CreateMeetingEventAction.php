<?php

namespace App\Actions;

use App\Domain\Booking\Booking;
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
        $data = $this->hydrateFromBooking($data);

        if (! empty($data['existing_meeting_id'])) {
            return MeetingEvent::withoutGlobalScope('hotel')->findOrFail($data['existing_meeting_id']);
        }

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

    private function hydrateFromBooking(array $data): array
    {
        if (empty($data['booking_id']) || ! empty($data['event_name'])) {
            return $data;
        }

        $booking = Booking::withoutGlobalScope('hotel')
            ->with(['meetingEvents.packageAssignments'])
            ->findOrFail($data['booking_id']);

        $meeting = $booking->meetingEvents->sortBy('start_at')->first();
        if ($meeting) {
            return array_merge($data, [
                'meeting_room_id' => $meeting->meeting_room_id,
                'event_name' => $meeting->event_name,
                'event_date' => $meeting->event_date,
                'start_at' => $meeting->start_at,
                'end_at' => $meeting->end_at,
                'expected_participants' => $meeting->expected_participants,
                'actual_participants' => $meeting->actual_participants,
                'status' => $meeting->status->value ?? $meeting->status,
                'existing_meeting_id' => $meeting->id,
            ]);
        }

        return $data;
    }
}
