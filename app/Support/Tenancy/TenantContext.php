<?php

namespace App\Support\Tenancy;

use App\Domain\Hotel\Hotel;

class TenantContext
{
    private ?Hotel $hotel = null;

    private bool $bypassed = false;

    public function set(?Hotel $hotel): void
    {
        $this->hotel = $hotel;
        $this->bypassed = false;
    }

    public function hotel(): ?Hotel
    {
        return $this->hotel;
    }

    public function hotelId(): ?int
    {
        return $this->hotel?->id;
    }

    public function bypass(): void
    {
        $this->bypassed = true;
    }

    public function isBypassed(): bool
    {
        return $this->bypassed;
    }
}
