<?php

namespace App\Domain\Redemption;

use App\Domain\Catering\MealSession;
use App\Domain\Meeting\MeetingEvent;
use App\Domain\Participant\Participant;
use App\Enums\RedemptionStatus;
use App\Enums\RejectionCode;
use App\Support\Tenancy\ScopeByHotel;
use Illuminate\Database\Eloquent\Model;

class Redemption extends Model
{
    use ScopeByHotel;

    public const UPDATED_AT = null;

    protected $fillable = [
        'hotel_id',
        'participant_id',
        'meeting_event_id',
        'meal_session_id',
        'participant_entitlement_id',
        'original_redemption_id',
        'redemption_number',
        'redeemed_at',
        'scanned_by',
        'device_id',
        'idempotency_key',
        'status',
        'rejection_code',
        'override_reason',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'redeemed_at' => 'datetime',
        'status' => RedemptionStatus::class,
        'rejection_code' => RejectionCode::class,
        'metadata' => 'array',
    ];

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function meetingEvent()
    {
        return $this->belongsTo(MeetingEvent::class);
    }

    public function mealSession()
    {
        return $this->belongsTo(MealSession::class);
    }

    public function participantEntitlement()
    {
        return $this->belongsTo(ParticipantEntitlement::class);
    }

    public function originalRedemption()
    {
        return $this->belongsTo(self::class, 'original_redemption_id');
    }

    public function overrideRedemptions()
    {
        return $this->hasMany(self::class, 'original_redemption_id');
    }
}
