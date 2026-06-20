<?php

namespace App\Domain\Meeting;

use App\Domain\Hotel\Hotel;
use App\Enums\RoomOperationalStatus;
use App\Support\Tenancy\ScopeByHotel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingRoom extends Model
{
    use HasFactory;
    use ScopeByHotel;

    protected $fillable = [
        'hotel_id',
        'code',
        'name',
        'floor',
        'capacity',
        'operational_status',
        'facilities',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'facilities' => 'array',
        'operational_status' => RoomOperationalStatus::class,
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
