<?php

namespace App\Domain\Participant;

use App\Domain\Hotel\Hotel;
use App\Domain\Meeting\MeetingEvent;
use App\Domain\QRCode\ParticipantQRCredential;
use App\Domain\Redemption\ParticipantEntitlement;
use App\Enums\ParticipantStatus;
use App\Support\Tenancy\ScopeByHotel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;
    use ScopeByHotel;

    protected $fillable = [
        'hotel_id',
        'meeting_event_id',
        'participant_number',
        'full_name',
        'company_name',
        'email',
        'normalized_email',
        'phone',
        'normalized_phone',
        'identity_reference',
        'registration_source',
        'status',
        'registered_at',
        'checked_in_at',
        'metadata',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'metadata' => 'array',
        'status' => ParticipantStatus::class,
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function meetingEvent()
    {
        return $this->belongsTo(MeetingEvent::class);
    }

    public function qrCredentials()
    {
        return $this->hasMany(ParticipantQRCredential::class);
    }

    public function activeQrCredential()
    {
        return $this->hasOne(ParticipantQRCredential::class)->where('status', 'ACTIVE');
    }

    public function entitlements()
    {
        return $this->hasMany(ParticipantEntitlement::class);
    }
}
