<?php

namespace App\Domain\Booking;

use App\Domain\Hotel\Hotel;
use App\Support\Tenancy\ScopeByHotel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;
    use ScopeByHotel;

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
}
