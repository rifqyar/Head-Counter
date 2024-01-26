<?php

namespace App\Models\Module\MasterData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingSchedule extends Model
{
    use HasFactory;

    public $table = 'trx_meeting_schedule';
    protected $guarded = [];
    public $timestamp = false;
}
