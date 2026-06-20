<?php

namespace App\Domain\Catering;

use App\Domain\Hotel\Hotel;
use App\Support\Tenancy\ScopeByHotel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingPackage extends Model
{
    use HasFactory;
    use ScopeByHotel;

    protected $fillable = [
        'hotel_id',
        'code',
        'name',
        'description',
        'price',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function entitlements()
    {
        return $this->hasMany(PackageEntitlement::class, 'package_id');
    }
}
