<?php

namespace App\Policies;

use App\Domain\Integration\IntegrationApiKey;
use App\Models\User;

class IntegrationApiKeyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('integration.manage');
    }

    public function manage(User $user, ?IntegrationApiKey $key = null): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $user->can('integration.manage') || $user->hotel_id === null) {
            return false;
        }

        return $key === null || (int) $key->hotel_id === (int) $user->hotel_id;
    }
}
