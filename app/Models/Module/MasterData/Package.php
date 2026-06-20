<?php

namespace App\Models\Module\MasterData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    public $table = 'm_packages';

    protected $fillable = [
        'kd_pck',
        'name',
        'price',
        'details',
        'count_qr',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];
}
