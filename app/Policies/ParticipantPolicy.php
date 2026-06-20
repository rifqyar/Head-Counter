<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesHotelScopedModels;

class ParticipantPolicy
{
    use AuthorizesHotelScopedModels;
}
