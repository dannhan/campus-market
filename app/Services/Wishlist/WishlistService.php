<?php

namespace App\Services\Wishlist;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

/**
 * Domain service for managing product bookmarks/wishlists (SRS-017).
 */
class WishlistService
{
    /**
     * Toggle wishlist bookmark state for a given user and product.
     *
     * @return bool True if bookmarked, False if unbookmarked.
     *
     * @throws InvalidArgumentException
     */
    public function toggle(User $user, Product $product): bool
    {
        if ($product->user_id === $user->id) {
            throw new InvalidArgumentException('Anda tidak dapat menambahkan produk Anda sendiri ke wishlist.');
        }

        $existing = Wishlist::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return false;
        }

        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        return true;
    }

    /**
     * Add a product to the user's wishlist.
     *
     * @throws InvalidArgumentException
     */
    public function add(User $user, Product $product): Wishlist
    {
        if ($product->user_id === $user->id) {
            throw new InvalidArgumentException('Anda tidak dapat menambahkan produk Anda sendiri ke wishlist.');
        }

        /** @var Wishlist $wishlist */
        $wishlist = Wishlist::firstOrCreate([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        return $wishlist;
    }

    /**
     * Remove a product from the user's wishlist.
     */
    public function remove(User $user, Product $product): bool
    {
        return (bool) Wishlist::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->delete();
    }

    /**
     * Determine if a product is saved in the user's wishlist.
     */
    public function isWishlisted(User $user, Product $product): bool
    {
        return Wishlist::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->exists();
    }

    /**
     * Paginate the user's wishlisted items with eager loaded relations.
     */
    public function getUserWishlist(User $user, int $perPage = 12): LengthAwarePaginator
    {
        return Wishlist::query()
            ->where('user_id', $user->id)
            ->whereHas('product', fn ($query) => $query->where('status', '!=', 'withdrawn'))
            ->with([
                'product.category',
                'product.images',
                'product.user.student.university',
            ])
            ->latest()
            ->paginate($perPage);
    }
}
