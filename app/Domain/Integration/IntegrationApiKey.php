<?php

namespace App\Domain\Integration;

use App\Domain\Hotel\Hotel;
use App\Models\User;
use App\Support\Tenancy\ScopeByHotel;
use Illuminate\Database\Eloquent\Model;

class IntegrationApiKey extends Model
{
    use ScopeByHotel;

    protected $fillable = [
        'hotel_id',
        'name',
        'key_prefix',
        'secret_hash',
        'abilities',
        'status',
        'last_used_at',
        'expires_at',
        'created_by',
        'revoked_at',
        'revoked_by',
    ];

    protected $casts = [
        'abilities' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    protected $hidden = [
        'secret_hash',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isActive(): bool
    {
        return $this->status === 'ACTIVE'
            && $this->revoked_at === null
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
