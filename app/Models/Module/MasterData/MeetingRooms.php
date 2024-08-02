<?php

namespace App\Models\Module\MasterData;

use App\Models\Module\Setting\RoomStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingRooms extends Model
{
    use HasFactory;

    public $table = 'm_meeting_rooms';
    protected $guarded = [];
    public $timestamp = false;

    function status()
    {
        return $this->hasOne(RoomStatus::class, 'kd_status', 'room_availability');
    }
}
