<?php

namespace App\Support\Tenancy;

use Illuminate\Database\Eloquent\Builder;

trait ScopeByHotel
{
    protected static function bootScopeByHotel(): void
    {
        static::addGlobalScope('hotel', function (Builder $builder) {
            $tenant = app(TenantContext::class);

            if ($tenant->isBypassed() || $tenant->hotelId() === null) {
                return;
            }

            $builder->where($builder->getModel()->getTable().'.hotel_id', $tenant->hotelId());
        });

        static::creating(function ($model) {
            $tenant = app(TenantContext::class);

            if (! $model->hotel_id && ! $tenant->isBypassed() && $tenant->hotelId() !== null) {
                $model->hotel_id = $tenant->hotelId();
            }
        });
    }

    public function scopeForHotel(Builder $query, int $hotelId): Builder
    {
        return $query->withoutGlobalScope('hotel')->where($this->getTable().'.hotel_id', $hotelId);
    }
}
