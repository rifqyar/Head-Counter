<?php

namespace App\Domain\Catering;

use App\Domain\Meeting\MeetingEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingPackageAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_event_id',
        'package_id',
        'participant_quota',
        'unit_price',
        'notes',
    ];

    protected $casts = [
        'participant_quota' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    public function meetingEvent()
    {
        return $this->belongsTo(MeetingEvent::class);
    }

    public function package()
    {
        return $this->belongsTo(MeetingPackage::class, 'package_id');
    }
}
