<?php

namespace App\Domain\Hotel;

use App\Enums\HotelStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'address',
        'timezone',
        'status',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'status' => HotelStatus::class,
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
