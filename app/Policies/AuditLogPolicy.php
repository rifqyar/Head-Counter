<?php

namespace App\Policies;

use App\Domain\Audit\AuditLog;
use App\Models\User;

class AuditLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('audit.view');
    }

    public function view(User $user, AuditLog $auditLog): bool
    {
        if (! $this->viewAny($user)) {
            return false;
        }

        return $user->isSuperAdmin() || (int) $user->hotel_id === (int) $auditLog->hotel_id;
    }
}
