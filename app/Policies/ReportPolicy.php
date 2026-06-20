<?php

namespace App\Policies;

use App\Models\User;

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('report.view');
    }

    public function export(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('report.export');
    }
}
