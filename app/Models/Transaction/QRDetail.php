<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QRDetail extends Model
{
    use HasFactory;

    public $table = 'qr_detail';

    protected $fillable = [
        'meeting_id',
        'qr_path',
        'qr_valid_start',
        'qr_valid_end',
    ];

    protected $casts = [
        'qr_valid_start' => 'datetime',
        'qr_valid_end' => 'datetime',
    ];
}
