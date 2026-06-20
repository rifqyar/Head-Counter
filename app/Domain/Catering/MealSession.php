<?php

namespace App\Domain\Catering;

use App\Domain\Hotel\Hotel;
use App\Domain\Meeting\MeetingEvent;
use App\Enums\EntitlementType;
use App\Enums\MealSessionStatus;
use App\Support\Tenancy\ScopeByHotel;
use Illuminate\Database\Eloquent\Model;

class MealSession extends Model
{
    use ScopeByHotel;

    protected $fillable = [
        'hotel_id',
        'meeting_event_id',
        'entitlement_type',
        'session_number',
        'name',
        'starts_at',
        'ends_at',
        'status',
        'location',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'entitlement_type' => EntitlementType::class,
        'session_number' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'status' => MealSessionStatus::class,
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function meetingEvent()
    {
        return $this->belongsTo(MeetingEvent::class);
    }
}
