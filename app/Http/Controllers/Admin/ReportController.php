<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\University;
use App\Models\User;
use App\Models\Wishlist;
use App\Services\Report\TransactionExportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    /**
     * Display transaction reports and platform usage statistics (SRS-012).
     */
    public function index(Request $request): View
    {
        // 1. User & Identity Statistics
        $totalUsers = User::count();
        $verifiedUsers = User::whereNotNull('email_verified_at')->count();
        $activeStudents = User::where('student_status', 'active')->count();
        $alumniUsers = User::where('student_status', 'alumni')->count();

        // 2. Product Marketplace Statistics
        $totalProducts = Product::count();
        $activeProducts = Product::where('status', 'active')->count();
        $soldProducts = Product::where('status', 'sold')->count();
        $withdrawnProducts = Product::where('status', 'withdrawn')->count();
        $totalViews = (int) Product::sum('views_count');

        $saleProductsCount = Product::where('transaction_type', 'sale')->count();
        $rentProductsCount = Product::where('transaction_type', 'rent')->count();

        // 3. Transactions & Bargaining Metrics
        $totalOffers = Offer::count();
        $acceptedOffersCount = Offer::where('status', 'accepted')->count();
        $pendingOffersCount = Offer::where('status', 'pending')->count();
        $rejectedOffersCount = Offer::where('status', 'rejected')->count();

        // Direct checkout transactions
        $totalTransactionsCount = Transaction::count();
        $completedTransactionsCount = Transaction::where('status', Transaction::STATUS_COMPLETED)->count();
        $totalTransactionAmount = (float) Transaction::where('status', Transaction::STATUS_COMPLETED)->sum('total_amount');

        // Gross Merchandise Value (GMV) estimate: sold products + accepted offers + completed transactions
        $soldProductsValue = (float) Product::where('status', 'sold')->sum('price');
        $acceptedOffersValue = (float) Offer::where('status', 'accepted')->sum('offered_price');
        $totalEstimatedGmv = $soldProductsValue + $acceptedOffersValue + $totalTransactionAmount;

        // 4. Student Engagement & Communication
        $totalConversations = Conversation::count();
        $totalMessages = Message::count();
        $totalWishlists = Wishlist::count();

        // 5. University Distribution (Users per campus)
        $universitiesStats = University::query()
            ->withCount('students')
            ->orderByDesc('students_count')
            ->get();

        // 6. Category Breakdown
        $categoriesStats = Category::query()
            ->withCount('products')
            ->orderByDesc('products_count')
            ->get();

        // 7. Transaction Report / Bargaining Records (with filter)
        $statusFilter = $request->query('status');

        $offersQuery = Offer::query()
            ->with([
                'product.user.student.university',
                'product.category',
                'user.student.university',
            ])
            ->latest();

        if ($statusFilter && in_array($statusFilter, Offer::STATUSES, true)) {
            $offersQuery->where('status', $statusFilter);
        }

        $transactions = $offersQuery->paginate(15)->withQueryString();

        return view('admin.reports.index', [
            // User stats
            'totalUsers' => $totalUsers,
            'verifiedUsers' => $verifiedUsers,
            'activeStudents' => $activeStudents,
            'alumniUsers' => $alumniUsers,
            'universitiesStats' => $universitiesStats,

            // Product stats
            'totalProducts' => $totalProducts,
            'activeProducts' => $activeProducts,
            'soldProducts' => $soldProducts,
            'withdrawnProducts' => $withdrawnProducts,
            'totalViews' => $totalViews,
            'saleProductsCount' => $saleProductsCount,
            'rentProductsCount' => $rentProductsCount,
            'categoriesStats' => $categoriesStats,

            // Transaction stats
            'totalOffers' => $totalOffers,
            'acceptedOffersCount' => $acceptedOffersCount,
            'pendingOffersCount' => $pendingOffersCount,
            'rejectedOffersCount' => $rejectedOffersCount,
            'soldProductsValue' => $soldProductsValue,
            'acceptedOffersValue' => $acceptedOffersValue,
            'totalTransactionsCount' => $totalTransactionsCount,
            'completedTransactionsCount' => $completedTransactionsCount,
            'totalTransactionAmount' => $totalTransactionAmount,
            'totalEstimatedGmv' => $totalEstimatedGmv,

            // Engagement
            'totalConversations' => $totalConversations,
            'totalMessages' => $totalMessages,
            'totalWishlists' => $totalWishlists,

            // Transaction Records & Filters
            'transactions' => $transactions,
            'currentStatus' => $statusFilter,
        ]);
    }

    /**
     * Export transaction reports to Excel / CSV format.
     */
    public function export(Request $request, TransactionExportService $exportService): Response
    {
        $format = strtolower((string) $request->input('format', 'xlsx'));
        if (! in_array($format, ['xlsx', 'xls', 'csv'], true)) {
            $format = 'xlsx';
        }

        $query = $exportService->buildQuery($request);
        $timestamp = now()->format('Ymd_His');

        if ($format === 'csv') {
            $content = $exportService->exportCsv($query);
            $filename = "laporan_transaksi_campusmarket_{$timestamp}.csv";

            return response($content, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
        }

        $content = $exportService->exportExcelXml($query);
        $filename = "laporan_transaksi_campusmarket_{$timestamp}.{$format}";

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
