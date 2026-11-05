<?php

namespace App\Http\Controllers;

use App\Models\EscrowPayment;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SellerDashboardController extends Controller
{
    /**
     * Display the main Seller Center dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // 1. Sales and Revenue Statistics
        $totalRevenue = (float) Transaction::where('seller_id', $user->id)
            ->where('status', Transaction::STATUS_COMPLETED)
            ->sum('amount');

        $escrowPendingBalance = (float) EscrowPayment::whereHas('transaction', function ($q) use ($user) {
            $q->where('seller_id', $user->id);
        })->where('status', EscrowPayment::STATUS_HELD)->sum('amount');

        $completedOrdersCount = Transaction::where('seller_id', $user->id)
            ->where('status', Transaction::STATUS_COMPLETED)
            ->count();

        // Orders that require seller action: processing or paid waiting for shipment/completion
        $actionRequiredCount = Transaction::where('seller_id', $user->id)
            ->where(function ($q) {
                $q->where('status', Transaction::STATUS_PROCESSING)
                    ->orWhere(function ($sub) {
                        $sub->where('status', Transaction::STATUS_PENDING)
                            ->where('payment_status', Transaction::PAYMENT_STATUS_PAID);
                    });
            })
            ->count();

        // 2. Products / Inventory Statistics
        $activeProductsCount = Product::where('user_id', $user->id)->where('status', 'active')->count();
        $soldProductsCount = Product::where('user_id', $user->id)->where('status', 'sold')->count();
        $totalProductsCount = Product::where('user_id', $user->id)->count();

        // Total product views across all seller listings
        $totalProductViews = (int) Product::where('user_id', $user->id)->sum('views_count');

        // 3. Incoming Pending Offers
        $pendingOffers = Offer::whereHas('product', fn ($q) => $q->where('user_id', $user->id))
            ->where('status', 'pending')
            ->where('user_id', '!=', $user->id)
            ->with(['product.images', 'user.student.university'])
            ->latest()
            ->take(5)
            ->get();

        // 4. Recent Seller Orders
        $recentOrders = Transaction::where('seller_id', $user->id)
            ->with(['product.images', 'buyer.student.university', 'escrowPayment'])
            ->latest()
            ->take(5)
            ->get();

        return view('seller.dashboard', [
            'user' => $user,
            'totalRevenue' => $totalRevenue,
            'formattedTotalRevenue' => 'Rp '.number_format($totalRevenue, 0, ',', '.'),
            'escrowPendingBalance' => $escrowPendingBalance,
            'formattedEscrowPendingBalance' => 'Rp '.number_format($escrowPendingBalance, 0, ',', '.'),
            'completedOrdersCount' => $completedOrdersCount,
            'actionRequiredCount' => $actionRequiredCount,
            'activeProductsCount' => $activeProductsCount,
            'soldProductsCount' => $soldProductsCount,
            'totalProductsCount' => $totalProductsCount,
            'totalProductViews' => $totalProductViews,
            'pendingOffers' => $pendingOffers,
            'pendingOffersCount' => $pendingOffers->count(),
            'recentOrders' => $recentOrders,
            'hasPaymentSettings' => $user->hasPaymentSettings(),
        ]);
    }

    /**
     * Display dedicated seller orders management page with filters.
     */
    public function orders(Request $request): View
    {
        $user = $request->user();
        $statusFilter = $request->query('status', 'all');

        $query = Transaction::where('seller_id', $user->id)
            ->with(['product.images', 'buyer.student.university', 'escrowPayment'])
            ->latest();

        if ($statusFilter === 'action_required') {
            $query->where(function ($q) {
                $q->where('status', Transaction::STATUS_PROCESSING)
                    ->orWhere(function ($sub) {
                        $sub->where('status', Transaction::STATUS_PENDING)
                            ->where('payment_status', Transaction::PAYMENT_STATUS_PAID);
                    });
            });
        } elseif ($statusFilter === 'processing') {
            $query->where('status', Transaction::STATUS_PROCESSING);
        } elseif ($statusFilter === 'completed') {
            $query->where('status', Transaction::STATUS_COMPLETED);
        } elseif ($statusFilter === 'cancelled') {
            $query->where('status', Transaction::STATUS_CANCELLED);
        }

        $orders = $query->paginate(15)->withQueryString();

        $actionRequiredCount = Transaction::where('seller_id', $user->id)
            ->where(function ($q) {
                $q->where('status', Transaction::STATUS_PROCESSING)
                    ->orWhere(function ($sub) {
                        $sub->where('status', Transaction::STATUS_PENDING)
                            ->where('payment_status', Transaction::PAYMENT_STATUS_PAID);
                    });
            })
            ->count();

        return view('seller.orders', [
            'orders' => $orders,
            'statusFilter' => $statusFilter,
            'actionRequiredCount' => $actionRequiredCount,
            'totalCount' => Transaction::where('seller_id', $user->id)->count(),
            'completedCount' => Transaction::where('seller_id', $user->id)->where('status', Transaction::STATUS_COMPLETED)->count(),
        ]);
    }

    /**
     * Display seller product catalog / inventory with quick controls.
     */
    public function products(Request $request): View
    {
        $user = $request->user();
        $filter = $request->query('status', 'all');

        $query = Product::where('user_id', $user->id)
            ->with(['images', 'category'])
            ->withCount(['offers' => fn ($q) => $q->where('status', 'pending')])
            ->latest();

        if ($filter === 'active') {
            $query->where('status', 'active');
        } elseif ($filter === 'sold') {
            $query->where('status', 'sold');
        } elseif ($filter === 'withdrawn') {
            $query->where('status', 'withdrawn');
        }

        $products = $query->paginate(15)->withQueryString();

        return view('seller.products', [
            'products' => $products,
            'filter' => $filter,
            'totalCount' => Product::where('user_id', $user->id)->count(),
            'activeCount' => Product::where('user_id', $user->id)->where('status', 'active')->count(),
            'soldCount' => Product::where('user_id', $user->id)->where('status', 'sold')->count(),
            'withdrawnCount' => Product::where('user_id', $user->id)->where('status', 'withdrawn')->count(),
        ]);
    }

    /**
     * Toggle product status (archive/withdraw or activate).
     */
    public function toggleProductStatus(Request $request, Product $product): RedirectResponse
    {
        $user = $request->user();

        if ($product->user_id !== $user->id) {
            abort(403, 'Anda tidak berhak mengubah status produk ini.');
        }

        $newStatus = $product->status === 'active' ? 'withdrawn' : 'active';
        $product->update(['status' => $newStatus]);

        $message = $newStatus === 'active'
            ? "Produk '{$product->title}' berhasil diaktifkan kembali ke etalase."
            : "Produk '{$product->title}' berhasil ditarik sementara dari etalase.";

        return back()->with('status', $message);
    }
}
