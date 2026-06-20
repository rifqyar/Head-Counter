<?php

namespace App\Models\Module\MasterData;

use App\Models\Module\Transaction\MeetingAttendance;
use App\Models\Transaction\QRDetail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingSchedule extends Model
{
    use HasFactory;

    public $table = 'trx_meeting_schedule';

    protected $fillable = [
        'trx_number',
        'code_client',
        'tgl_start',
        'tgl_end',
        'jam_mulai',
        'jam_selesai',
        'kuota',
        'qr_path',
        'package',
        'room',
    ];

    public function room()
    {
        return $this->hasOne(MeetingRooms::class, 'kd_room', 'room');
    }

    public function ruangan()
    {
        return $this->room();
    }

    public function package()
    {
        return $this->hasOne(Package::class, 'kd_pck', 'package');
    }

    public function paket()
    {
        return $this->package();
    }

    public function qr()
    {
        return $this->hasMany(QRDetail::class, 'meeting_id', 'id');
    }

    public function attendance()
    {
        return $this->hasMany(MeetingAttendance::class, 'trx_metting_number', 'trx_number');
    }
}
