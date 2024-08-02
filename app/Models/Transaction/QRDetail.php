<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QRDetail extends Model
{
    use HasFactory;
    public $table = 'qr_detail';
    protected $guarded = [];
    public $timestamp = false;
}
