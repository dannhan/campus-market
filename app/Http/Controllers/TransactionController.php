<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutTransactionRequest;
use App\Http\Requests\UploadPaymentProofRequest;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TransactionController extends Controller
{
    /**
     * Display a listing of user transactions (purchases & sales).
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $statusFilter = $request->query('status');
        $methodFilter = $request->query('method');

        $purchasesQuery = Transaction::query()
            ->where('buyer_id', $user->id)
            ->with(['product.images', 'seller.student.university'])
            ->latest();

        $salesQuery = Transaction::query()
            ->where('seller_id', $user->id)
            ->with(['product.images', 'buyer.student.university'])
            ->latest();

        if ($statusFilter && in_array($statusFilter, Transaction::STATUSES, true)) {
            $purchasesQuery->where('status', $statusFilter);
            $salesQuery->where('status', $statusFilter);
        }

        if ($methodFilter && in_array($methodFilter, array_keys(Transaction::PAYMENT_METHODS), true)) {
            $purchasesQuery->where('payment_method', $methodFilter);
            $salesQuery->where('payment_method', $methodFilter);
        }

        return view('transactions.index', [
            'purchases' => $purchasesQuery->paginate(10, ['*'], 'purchases_page')->withQueryString(),
            'sales' => $salesQuery->paginate(10, ['*'], 'sales_page')->withQueryString(),
            'statusFilter' => $statusFilter,
            'methodFilter' => $methodFilter,
            'paymentMethods' => Transaction::PAYMENT_METHODS,
            'bankChannels' => Transaction::BANK_CHANNELS,
            'ewalletChannels' => Transaction::EWALLET_CHANNELS,
        ]);
    }

    /**
     * Show the checkout page with student-friendly payment methods.
     */
    public function checkout(Request $request, Product $product): View
    {
        $user = $request->user();

        if ($product->user_id === $user->id) {
            abort(403, 'Anda tidak dapat membeli produk milik Anda sendiri.');
        }

        $offer = null;
        $price = (float) $product->price;

        if ($request->filled('offer_id')) {
            $offer = Offer::where('id', $request->query('offer_id'))
                ->where('product_id', $product->id)
                ->where('user_id', $user->id)
                ->where('status', 'accepted')
                ->firstOrFail();

            $price = (float) $offer->offered_price;
        } elseif ($product->status !== 'active') {
            abort(400, 'Produk ini tidak lagi aktif untuk dibeli.');
        }

        $seller = $product->user->load('student.university');

        return view('transactions.checkout', [
            'product' => $product,
            'offer' => $offer,
            'price' => $price,
            'seller' => $seller,
            'paymentMethods' => Transaction::PAYMENT_METHODS,
            'bankChannels' => Transaction::BANK_CHANNELS,
            'ewalletChannels' => Transaction::EWALLET_CHANNELS,
        ]);
    }

    /**
     * Store a newly created transaction order.
     */
    public function store(CheckoutTransactionRequest $request): RedirectResponse
    {
        $user = $request->user();
        $product = Product::with('user')->findOrFail($request->validated('product_id'));

        if ($product->user_id === $user->id) {
            abort(403, 'Anda tidak dapat membeli produk milik Anda sendiri.');
        }

        $offer = null;
        $price = (float) $product->price;

        if ($request->filled('offer_id')) {
            $offer = Offer::where('id', $request->validated('offer_id'))
                ->where('product_id', $product->id)
                ->where('user_id', $user->id)
                ->where('status', 'accepted')
                ->firstOrFail();

            $price = (float) $offer->offered_price;
        } elseif ($product->status !== 'active') {
            return back()->with('error', 'Produk ini sudah tidak aktif atau telah terjual.');
        }

        $seller = $product->user;
        $paymentMethod = $request->validated('payment_method');
        $paymentChannel = $request->validated('payment_channel');

        // Snapshot seller's payment details
        $paymentDetails = [
            'bank_name' => $seller->bank_name,
            'bank_name_label' => $seller->bank_name_label,
            'bank_account_number' => $seller->bank_account_number,
            'bank_account_name' => $seller->bank_account_name,
            'ewallet_provider' => $seller->ewallet_provider,
            'ewallet_provider_label' => $seller->ewallet_provider_label,
            'ewallet_account_number' => $seller->ewallet_account_number,
            'ewallet_account_name' => $seller->ewallet_account_name,
            'preferred_meeting_location' => $seller->preferred_meeting_location,
        ];

        // For COD, the transaction starts in processing status (waiting for meetup)
        $status = $paymentMethod === Transaction::PAYMENT_METHOD_COD
            ? Transaction::STATUS_PROCESSING
            : Transaction::STATUS_PENDING;

        $transaction = DB::transaction(function () use ($product, $offer, $user, $seller, $price, $paymentMethod, $paymentChannel, $request, $paymentDetails, $status) {
            $trx = Transaction::create([
                'product_id' => $product->id,
                'offer_id' => $offer?->id,
                'buyer_id' => $user->id,
                'seller_id' => $seller->id,
                'amount' => $price,
                'payment_method' => $paymentMethod,
                'payment_channel' => $paymentChannel,
                'payment_status' => Transaction::PAYMENT_STATUS_PENDING,
                'status' => $status,
                'meeting_location' => $request->validated('meeting_location'),
                'buyer_notes' => $request->validated('buyer_notes'),
                'payment_details' => $paymentDetails,
            ]);

            $product->update(['status' => 'sold']);

            return $trx;
        });

        $message = match ($paymentMethod) {
            Transaction::PAYMENT_METHOD_COD => 'Pesanan COD berhasil dibuat. Silakan atur waktu pertemuan di kampus.',
            Transaction::PAYMENT_METHOD_BANK_TRANSFER => 'Pesanan berhasil dibuat. Silakan selesaikan transfer bank ke rekening penjual.',
            Transaction::PAYMENT_METHOD_EWALLET => 'Pesanan berhasil dibuat. Silakan lakukan transfer saldo e-wallet ke nomor penjual.',
            default => 'Pesanan berhasil dibuat.',
        };

        return redirect()->route('transactions.show', $transaction)->with('status', $message);
    }

    /**
     * Display transaction details and payment instructions.
     */
    public function show(Request $request, Transaction $transaction): View
    {
        $user = $request->user();

        if ($transaction->buyer_id !== $user->id && $transaction->seller_id !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Anda tidak memiliki hak untuk melihat detail transaksi ini.');
        }

        $transaction->load([
            'product.images',
            'product.category',
            'buyer.student.university',
            'seller.student.university',
            'offer',
        ]);

        return view('transactions.show', [
            'transaction' => $transaction,
            'isBuyer' => $user->id === $transaction->buyer_id,
            'isSeller' => $user->id === $transaction->seller_id,
        ]);
    }

    /**
     * Upload proof of payment for bank transfer or e-wallet.
     */
    public function uploadProof(UploadPaymentProofRequest $request, Transaction $transaction): RedirectResponse
    {
        $user = $request->user();

        if ($transaction->buyer_id !== $user->id) {
            abort(403, 'Hanya pembeli yang dapat mengunggah bukti pembayaran.');
        }

        if (in_array($transaction->status, [Transaction::STATUS_COMPLETED, Transaction::STATUS_CANCELLED], true)) {
            return back()->with('error', 'Status transaksi saat ini tidak mengizinkan pengunggahan bukti pembayaran.');
        }

        if ($request->hasFile('payment_proof') && $request->file('payment_proof')->isValid()) {
            if ($transaction->payment_proof && Storage::disk('public')->exists($transaction->payment_proof)) {
                Storage::disk('public')->delete($transaction->payment_proof);
            }

            $path = $request->file('payment_proof')->store('payment_proofs', 'public');

            $transaction->update([
                'payment_proof' => $path,
                'payment_status' => Transaction::PAYMENT_STATUS_PAID,
                'status' => Transaction::STATUS_PROCESSING,
                'paid_at' => now(),
                'buyer_notes' => $request->validated('buyer_notes') ?? $transaction->buyer_notes,
            ]);
        }

        return back()->with('status', 'Bukti pembayaran berhasil diunggah. Menunggu konfirmasi penerimaan dari penjual.');
    }

    /**
     * Seller confirms that the payment has been received.
     */
    public function confirm(Request $request, Transaction $transaction): RedirectResponse
    {
        $user = $request->user();

        if ($transaction->seller_id !== $user->id) {
            abort(403, 'Hanya penjual yang dapat mengonfirmasi penerimaan pembayaran.');
        }

        if ($transaction->status === Transaction::STATUS_COMPLETED) {
            return back()->with('error', 'Transaksi ini telah selesai sebelumnya.');
        }

        $transaction->update([
            'payment_status' => Transaction::PAYMENT_STATUS_CONFIRMED,
            'status' => Transaction::STATUS_COMPLETED,
            'confirmed_at' => now(),
        ]);

        return back()->with('status', 'Pembayaran berhasil diverifikasi. Transaksi jual-beli telah selesai.');
    }

    /**
     * Cancel a pending transaction before payment is finalized.
     */
    public function cancel(Request $request, Transaction $transaction): RedirectResponse
    {
        $user = $request->user();

        if ($transaction->buyer_id !== $user->id && $transaction->seller_id !== $user->id) {
            abort(403, 'Anda tidak berhak membatalkan transaksi ini.');
        }

        if ($transaction->status === Transaction::STATUS_COMPLETED) {
            return back()->with('error', 'Transaksi yang telah selesai tidak dapat dibatalkan.');
        }

        DB::transaction(function () use ($transaction) {
            $transaction->update([
                'status' => Transaction::STATUS_CANCELLED,
                'payment_status' => Transaction::PAYMENT_STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);

            // Reactivate the product so it is available again in the marketplace
            $transaction->product->update(['status' => 'active']);
        });

        return back()->with('status', 'Transaksi berhasil dibatalkan dan produk dikembalikan ke etalase.');
    }
}
