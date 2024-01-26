<?php

namespace App\Models\Module\MasterData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    public $table = 'm_client';
    protected $guarded = [];
    public $timestamp = false;
}
