<?php

namespace App\Domain\Catering;

use App\Enums\EntitlementType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageEntitlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id',
        'entitlement_type',
        'quantity',
        'metadata',
    ];

    protected $casts = [
        'entitlement_type' => EntitlementType::class,
        'quantity' => 'integer',
        'metadata' => 'array',
    ];

    public function package()
    {
        return $this->belongsTo(MeetingPackage::class, 'package_id');
    }
}
