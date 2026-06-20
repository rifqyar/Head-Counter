<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesHotelScopedModels;

class BookingPolicy
{
    use AuthorizesHotelScopedModels;

    protected array $permissions = [
        'view' => 'booking.view',
        'create' => 'booking.create',
        'update' => 'booking.update',
        'delete' => 'booking.cancel',
    ];
}
