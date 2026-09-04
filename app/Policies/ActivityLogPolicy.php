<?php

namespace App\Policies;

use App\Models\User;

class ActivityLogPolicy
{
    /**
     * Only admin users can view the activity log.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }
}
