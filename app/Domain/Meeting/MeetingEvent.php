<?php

namespace App\Domain\Meeting;

use App\Domain\Attendance\MeetingAttendance;
use App\Domain\Booking\Booking;
use App\Domain\Catering\MealSession;
use App\Domain\Catering\MeetingPackageAssignment;
use App\Domain\Hotel\Hotel;
use App\Domain\Participant\Participant;
use App\Enums\MeetingStatus;
use App\Models\User;
use App\Support\Tenancy\ScopeByHotel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingEvent extends Model
{
    use HasFactory;
    use ScopeByHotel;

    protected $fillable = [
        'hotel_id',
        'booking_id',
        'meeting_room_id',
        'event_name',
        'event_date',
        'start_at',
        'end_at',
        'expected_participants',
        'actual_participants',
        'status',
        'legacy_trx_number',
        'meeting_qr_token_hash',
        'meeting_qr_token_last_four',
        'meeting_qr_issued_at',
        'meeting_qr_expires_at',
        'meeting_qr_revoked_at',
        'meeting_qr_path',
        'checkin_open_at',
        'checkin_close_at',
        'started_at',
        'completed_at',
        'cancelled_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'event_date' => 'date',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'checkin_open_at' => 'datetime',
        'checkin_close_at' => 'datetime',
        'meeting_qr_issued_at' => 'datetime',
        'meeting_qr_expires_at' => 'datetime',
        'meeting_qr_revoked_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'expected_participants' => 'integer',
        'actual_participants' => 'integer',
        'status' => MeetingStatus::class,
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function meetingRoom()
    {
        return $this->belongsTo(MeetingRoom::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function packageAssignments()
    {
        return $this->hasMany(MeetingPackageAssignment::class);
    }

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    public function attendances()
    {
        return $this->hasMany(MeetingAttendance::class);
    }

    public function mealSessions()
    {
        return $this->hasMany(MealSession::class);
    }
}
