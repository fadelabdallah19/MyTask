<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Only admin users can list all users.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }
}
