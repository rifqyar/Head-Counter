<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesHotelScopedModels;

class MealSessionPolicy
{
    use AuthorizesHotelScopedModels;

    protected array $permissions = [
        'view' => 'meal_session.view',
        'create' => 'meal_session.manage',
        'update' => 'meal_session.manage',
        'delete' => 'meal_session.manage',
    ];
}
