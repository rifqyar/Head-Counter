<?php

namespace App\Domain\Booking;

use App\Domain\Hotel\Hotel;
use App\Domain\Meeting\MeetingEvent;
use App\Enums\BookingStatus;
use App\Models\User;
use App\Support\Tenancy\ScopeByHotel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    use ScopeByHotel;

    protected $fillable = [
        'hotel_id',
        'external_booking_id',
        'client_id',
        'booking_number',
        'booking_source',
        'booking_date',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'status' => BookingStatus::class,
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function meetingEvents()
    {
        return $this->hasMany(MeetingEvent::class);
    }
}
