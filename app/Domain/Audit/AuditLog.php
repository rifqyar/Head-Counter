<?php

namespace App\Domain\Audit;

use App\Domain\Hotel\Hotel;
use App\Models\User;
use App\Support\Tenancy\ScopeByHotel;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use ScopeByHotel;

    public const UPDATED_AT = null;

    protected $fillable = [
        'hotel_id',
        'actor_type',
        'actor_id',
        'event',
        'action',
        'auditable_type',
        'auditable_id',
        'entity_type',
        'entity_id',
        'before_data',
        'after_data',
        'metadata',
        'ip_address',
        'user_agent',
        'request_id',
        'created_at',
    ];

    protected $casts = [
        'before_data' => 'array',
        'after_data' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
