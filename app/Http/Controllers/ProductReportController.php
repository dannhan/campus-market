<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductReportController extends Controller
{
    /**
     * Submit a report against a suspicious or fraudulent product.
     */
    public function store(Request $request, Product $product): RedirectResponse
    {
        $user = $request->user();

        if ($product->user_id === $user->id) {
            return back()->with('error', 'Anda tidak dapat melaporkan produk jualan Anda sendiri.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'in:'.implode(',', array_keys(ProductReport::REASONS))],
            'details' => ['nullable', 'string', 'max:1000'],
        ]);

        ProductReport::create([
            'product_id' => $product->id,
            'reporter_id' => $user->id,
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
            'status' => ProductReport::STATUS_PENDING,
        ]);

        // Automatically flag the product for admin moderation review
        if ($product->moderation_status !== Product::MODERATION_STATUS_REJECTED) {
            $product->update([
                'moderation_status' => Product::MODERATION_STATUS_FLAGGED,
            ]);
        }

        return back()->with('status', 'Laporan Anda berhasil dikirim. Tim moderator admin akan segera meninjau produk ini demi kenyamanan dan keamanan bersama.');
    }
}
