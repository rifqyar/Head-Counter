<?php

namespace App\Support\Reporting;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class HotelTimezoneService
{
    public function timezone(mixed $hotel = null): string
    {
        $timezone = $hotel?->timezone ?: config('app.timezone', 'UTC');

        return in_array($timezone, timezone_identifiers_list(), true) ? $timezone : config('app.timezone', 'UTC');
    }

    public function localNow(mixed $hotel = null): CarbonImmutable
    {
        return CarbonImmutable::now($this->timezone($hotel));
    }

    public function dayBounds(string $date, mixed $hotel = null): array
    {
        $timezone = $this->timezone($hotel);
        $start = CarbonImmutable::parse($date, $timezone)->startOfDay();

        return [$start->utc(), $start->endOfDay()->utc()];
    }

    public function rangeBounds(?string $from, ?string $to, mixed $hotel = null): array
    {
        $timezone = $this->timezone($hotel);
        $start = CarbonImmutable::parse($from ?: $this->localNow($hotel)->toDateString(), $timezone)->startOfDay();
        $end = CarbonImmutable::parse($to ?: $start->toDateString(), $timezone)->endOfDay();

        return [$start->utc(), $end->utc()];
    }

    public function display(mixed $date, mixed $hotel = null, string $format = 'Y-m-d H:i'): string
    {
        if (! $date) {
            return '-';
        }

        $value = $date instanceof CarbonInterface ? $date->copy() : CarbonImmutable::parse($date);

        return $value->timezone($this->timezone($hotel))->format($format);
    }
}
