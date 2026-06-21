<?php

namespace App\Support\Reporting;

use App\Domain\Hotel\Hotel;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Arr;

class ReportFilter
{
    public function __construct(
        public readonly User $user,
        public readonly ?Hotel $hotel,
        public readonly array $filters,
        public readonly string $timezone,
    ) {}

    public function hotelIds(): ?array
    {
        if ($this->hotel) {
            return [$this->hotel->id];
        }

        return $this->user->isSuperAdmin() ? null : [$this->user->hotel_id];
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->filters, $key, $default);
    }

    public static function from(User $user, array $input, HotelTimezoneService $timezones): self
    {
        $tenantHotel = app(TenantContext::class)->hotel();
        $hotel = null;

        if ($user->isSuperAdmin() && filled($input['hotel_id'] ?? null)) {
            $hotel = Hotel::whereKey($input['hotel_id'])->where('status', 'ACTIVE')->firstOrFail();
        } elseif (! $user->isSuperAdmin()) {
            $hotel = $tenantHotel ?: Hotel::whereKey($user->hotel_id)->first();
        } elseif ($tenantHotel) {
            $hotel = $tenantHotel;
        }

        return new self($user, $hotel, $input, $timezones->timezone($hotel));
    }
}
