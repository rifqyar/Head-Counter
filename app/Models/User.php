<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'hotel_id',
        'password',
        'status',
        'last_login_at',
        'deactivated_at',
        'deactivated_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
        'last_login_at' => 'datetime',
        'deactivated_at' => 'datetime',
    ];

    public function hotel()
    {
        return $this->belongsTo(\App\Domain\Hotel\Hotel::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasAnyRole(['SUPER_ADMIN', 'Super Admin', 'Administrator']);
    }

    public function isActive(): bool
    {
        return ($this->status ?? 'ACTIVE') === 'ACTIVE' && $this->deactivated_at === null;
    }
}
