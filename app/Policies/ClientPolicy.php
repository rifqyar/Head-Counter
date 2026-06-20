<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesHotelScopedModels;

class ClientPolicy
{
    use AuthorizesHotelScopedModels;
}
