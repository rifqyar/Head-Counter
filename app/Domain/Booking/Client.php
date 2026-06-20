<?php

namespace App\Domain\Booking;

use App\Domain\Hotel\Hotel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'external_id',
        'company_name',
        'contact_name',
        'contact_email',
        'contact_phone',
        'billing_address',
        'tax_number',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function hotels()
    {
        return $this->belongsToMany(Hotel::class, 'client_hotel')
            ->withPivot(['hotel_specific_code', 'status', 'notes', 'metadata'])
            ->withTimestamps();
    }

    public function scopeAssociatedWithHotel(Builder $query, int $hotelId): Builder
    {
        return $query->whereHas('hotels', fn (Builder $hotels) => $hotels->where('hotels.id', $hotelId));
    }
}
