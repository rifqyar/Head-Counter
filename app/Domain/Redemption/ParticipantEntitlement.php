<?php

namespace App\Domain\Redemption;

use App\Domain\Meeting\MeetingEvent;
use App\Domain\Participant\Participant;
use App\Enums\EntitlementType;
use Illuminate\Database\Eloquent\Model;

class ParticipantEntitlement extends Model
{
    protected $fillable = [
        'participant_id',
        'meeting_event_id',
        'entitlement_type',
        'total_quantity',
        'redeemed_quantity',
        'remaining_quantity',
    ];

    protected $casts = [
        'entitlement_type' => EntitlementType::class,
        'total_quantity' => 'integer',
        'redeemed_quantity' => 'integer',
        'remaining_quantity' => 'integer',
    ];

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function meetingEvent()
    {
        return $this->belongsTo(MeetingEvent::class);
    }
}
