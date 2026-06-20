<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesHotelScopedModels;

class BookingPolicy
{
    use AuthorizesHotelScopedModels;
}
