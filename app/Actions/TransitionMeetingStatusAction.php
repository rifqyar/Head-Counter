<?php

namespace App\Actions;

use App\Domain\Meeting\MeetingEvent;
use App\Enums\MeetingStatus;
use App\Services\MeetingStateTransition;

class TransitionMeetingStatusAction
{
    public function __construct(private readonly MeetingStateTransition $transitions) {}

    public function execute(MeetingEvent $meetingEvent, MeetingStatus|string $target): MeetingEvent
    {
        $status = $target instanceof MeetingStatus ? $target : MeetingStatus::from($target);

        return $this->transitions->transition($meetingEvent, $status);
    }
}
