<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Wishlist\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class WishlistController extends Controller
{
    public function __construct(protected WishlistService $wishlistService) {}

    /**
     * Display the authenticated student's wishlisted items (SRS-017).
     */
    public function index(Request $request): View
    {
        $wishlists = $this->wishlistService->getUserWishlist($request->user());

        return view('wishlist.index', [
            'wishlists' => $wishlists,
        ]);
    }

    /**
     * Toggle bookmark/wishlist status for a product.
     */
    public function toggle(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        try {
            $added = $this->wishlistService->toggle($request->user(), $product);
        } catch (InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }

        $message = $added
            ? 'Produk berhasil ditambahkan ke wishlist.'
            : 'Produk dihapus dari wishlist.';

        if ($request->wantsJson()) {
            return response()->json([
                'wishlisted' => $added,
                'message' => $message,
            ]);
        }

        return back()->with('status', $message);
    }

    /**
     * Remove a product from the authenticated user's wishlist.
     */
    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $this->wishlistService->remove($request->user(), $product);

        return back()->with('status', 'Produk berhasil dihapus dari wishlist.');
    }
}
