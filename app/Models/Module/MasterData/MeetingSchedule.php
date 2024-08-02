<?php

namespace App\Models\Module\MasterData;

use App\Models\Transaction\QRDetail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingSchedule extends Model
{
    use HasFactory;

    public $table = 'trx_meeting_schedule';
    protected $guarded = [];
    public $timestamp = false;

    public function ruangan(){
        return $this->hasOne(MeetingRooms::class, 'kd_room', 'room');
    }

    public function paket(){
        return $this->hasOne(Package::class, 'kd_pck', 'package');
    }

    public function qr(){
        return $this->hasMany(QRDetail::class, 'meeting_id', 'id');
    }
}
