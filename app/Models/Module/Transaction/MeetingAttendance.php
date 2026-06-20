<?php

namespace App\Models\Module\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingAttendance extends Model
{
    use HasFactory;

    public $table = 'trx_meeting_attendance';

    protected $fillable = [
        'trx_metting_number',
        'name',
        'phone_number',
        'jabatan',
        'company',
        'mac_address',
        'qr_path',
        'scanned_qr',
    ];

    public function meetingSchedule()
    {
        // Legacy typo retained until the Phase 3 schema refactor renames trx_metting_number.
        return $this->belongsTo(\App\Models\Module\MasterData\MeetingSchedule::class, 'trx_metting_number', 'trx_number');
    }
}
