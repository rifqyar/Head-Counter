<?php

namespace App\Models\Module\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingAttendance extends Model
{
    use HasFactory;

    public $table = 'trx_meeting_attendance';
    protected $guarded = [];
    public $timestamp = false;
}
