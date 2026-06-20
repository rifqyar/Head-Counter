<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesHotelScopedModels;

class MeetingPackagePolicy
{
    use AuthorizesHotelScopedModels;

    protected array $permissions = [
        'view' => 'meal_package.view',
        'create' => 'meal_package.manage',
        'update' => 'meal_package.manage',
        'delete' => 'meal_package.manage',
    ];
}
