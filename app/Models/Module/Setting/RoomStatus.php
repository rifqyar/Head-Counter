<?php

namespace App\Models\Module\Setting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomStatus extends Model
{
    use HasFactory;
    public $table = 'r_room_status';
    protected $guarded = [];
    public $timestamp = false;
}
