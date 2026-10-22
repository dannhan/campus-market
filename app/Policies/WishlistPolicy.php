<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wishlist;

class WishlistPolicy
{
    /**
     * Determine whether the user can view the wishlist entry.
     */
    public function view(User $user, Wishlist $wishlist): bool
    {
        return $wishlist->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the wishlist entry.
     */
    public function delete(User $user, Wishlist $wishlist): bool
    {
        return $wishlist->user_id === $user->id;
    }
}
