<?php

namespace App\Domain\Attendance;

use App\Domain\Meeting\MeetingEvent;
use App\Domain\Participant\Participant;
use App\Enums\AttendanceType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingAttendance extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'meeting_event_id',
        'participant_id',
        'attendance_type',
        'attended_at',
        'verification_method',
        'device_id',
        'scanned_by',
        'metadata',
    ];

    protected $casts = [
        'attended_at' => 'datetime',
        'metadata' => 'array',
        'attendance_type' => AttendanceType::class,
    ];

    public function meetingEvent()
    {
        return $this->belongsTo(MeetingEvent::class);
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function scanner()
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }
}
