<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductModerationController extends Controller
{
    /**
     * Display the moderation and verification queue for listings.
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->input('status', 'all');

        $metrics = [
            'total' => Product::count(),
            'verified' => Product::where('is_verified', true)->count(),
            'flagged' => Product::where('moderation_status', Product::MODERATION_STATUS_FLAGGED)->count(),
            'pending' => Product::where('moderation_status', Product::MODERATION_STATUS_PENDING)->count(),
            'rejected' => Product::where('moderation_status', Product::MODERATION_STATUS_REJECTED)->count(),
            'reports' => ProductReport::where('status', ProductReport::STATUS_PENDING)->count(),
        ];

        $query = Product::query()
            ->with(['category', 'user.student.university', 'images', 'reports'])
            ->withCount([
                'reports as pending_reports_count' => function ($q) {
                    $q->where('status', ProductReport::STATUS_PENDING);
                },
            ]);

        // Filter by status tab
        match ($statusFilter) {
            'flagged' => $query->where('moderation_status', Product::MODERATION_STATUS_FLAGGED),
            'pending' => $query->where('moderation_status', Product::MODERATION_STATUS_PENDING),
            'verified' => $query->where('is_verified', true),
            'rejected' => $query->where('moderation_status', Product::MODERATION_STATUS_REJECTED),
            'approved' => $query->where('moderation_status', Product::MODERATION_STATUS_APPROVED),
            default => null,
        };

        // Search
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by Category
        if ($categorySlug = $request->input('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
        }

        $products = $query->latest()->paginate(15)->withQueryString();

        return view('admin.moderation.index', [
            'products' => $products,
            'metrics' => $metrics,
            'statusFilter' => $statusFilter,
            'search' => $request->input('q', ''),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /**
     * Inspect product details and view associated fraud/violation reports.
     */
    public function show(Product $product): View
    {
        $product->load([
            'category',
            'user.student.university',
            'images',
            'reports.reporter.student.university',
            'moderator',
        ]);

        return view('admin.moderation.show', [
            'product' => $product,
        ]);
    }

    /**
     * Verify authentic campus product and award verified badge.
     */
    public function verify(Request $request, Product $product): RedirectResponse
    {
        $admin = $request->user();

        $product->update([
            'is_verified' => true,
            'moderation_status' => Product::MODERATION_STATUS_APPROVED,
            'moderated_at' => now(),
            'moderated_by_id' => $admin->id,
            'moderation_notes' => $request->input('notes', 'Lolos verifikasi keaslian dan keamanan produk kampus.'),
        ]);

        // Resolve pending reports
        $product->reports()->where('status', ProductReport::STATUS_PENDING)->update([
            'status' => ProductReport::STATUS_REVIEWED,
            'reviewed_by_id' => $admin->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('status', "Produk '{$product->title}' berhasil diverifikasi dan disematkan badge resmi.");
    }

    /**
     * Revoke official verified badge from product.
     */
    public function unverify(Request $request, Product $product): RedirectResponse
    {
        $product->update([
            'is_verified' => false,
        ]);

        return back()->with('status', "Badge verifikasi resmi pada produk '{$product->title}' telah dicabut.");
    }

    /**
     * Approve product (restore to active catalog).
     */
    public function approve(Request $request, Product $product): RedirectResponse
    {
        $admin = $request->user();

        $product->update([
            'moderation_status' => Product::MODERATION_STATUS_APPROVED,
            'moderated_at' => now(),
            'moderated_by_id' => $admin->id,
            'moderation_notes' => null,
        ]);

        $product->reports()->where('status', ProductReport::STATUS_PENDING)->update([
            'status' => ProductReport::STATUS_REVIEWED,
            'reviewed_by_id' => $admin->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('status', "Produk '{$product->title}' disetujui dan ditayangkan di etalase publik.");
    }

    /**
     * Reject or suspend product due to violation or fraud suspicion.
     */
    public function reject(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        $admin = $request->user();

        $product->update([
            'moderation_status' => Product::MODERATION_STATUS_REJECTED,
            'is_verified' => false,
            'moderated_at' => now(),
            'moderated_by_id' => $admin->id,
            'moderation_notes' => $validated['reason'],
        ]);

        $product->reports()->where('status', ProductReport::STATUS_PENDING)->update([
            'status' => ProductReport::STATUS_ACTION_TAKEN,
            'reviewed_by_id' => $admin->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('status', "Produk '{$product->title}' ditolak dan dicabut dari etalase publik.");
    }

    /**
     * Dismiss a student report as false alarm or resolved.
     */
    public function dismissReport(Request $request, Product $product, ProductReport $report): RedirectResponse
    {
        $admin = $request->user();

        $report->update([
            'status' => ProductReport::STATUS_DISMISSED,
            'reviewed_by_id' => $admin->id,
            'reviewed_at' => now(),
        ]);

        // If no more pending reports and product was flagged, return to approved
        if ($product->reports()->where('status', ProductReport::STATUS_PENDING)->count() === 0 && $product->isFlagged()) {
            $product->update(['moderation_status' => Product::MODERATION_STATUS_APPROVED]);
        }

        return back()->with('status', 'Laporan mahasiswa berhasil diabaikan.');
    }

    /**
     * Permanently delete a fraudulent or severely violating product.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $title = $product->title;
        $product->delete();

        return redirect()
            ->route('admin.moderation.index')
            ->with('status', "Produk '{$title}' berhasil dihapus secara permanen dari sistem.");
    }
}
