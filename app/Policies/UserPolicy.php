<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view the profile.
     */
    public function view(User $currentUser, User $targetUser): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the profile.
     */
    public function update(User $currentUser, User $targetUser): bool
    {
        return $currentUser->id === $targetUser->id;
    }
}
