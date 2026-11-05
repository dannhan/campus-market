<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutTransactionRequest;
use App\Http\Requests\ShipTransactionRequest;
use App\Http\Requests\UploadPaymentProofRequest;
use App\Models\EscrowPayment;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\University;
use App\Services\Shipping\CampusShippingRateService;
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
        $deliveryFilter = $request->query('delivery');

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

        if ($deliveryFilter && in_array($deliveryFilter, array_keys(Transaction::DELIVERY_METHODS), true)) {
            $purchasesQuery->where('delivery_method', $deliveryFilter);
            $salesQuery->where('delivery_method', $deliveryFilter);
        }

        return view('transactions.index', [
            'purchases' => $purchasesQuery->paginate(10, ['*'], 'purchases_page')->withQueryString(),
            'sales' => $salesQuery->paginate(10, ['*'], 'sales_page')->withQueryString(),
            'statusFilter' => $statusFilter,
            'methodFilter' => $methodFilter,
            'deliveryFilter' => $deliveryFilter,
            'paymentMethods' => Transaction::PAYMENT_METHODS,
            'bankChannels' => Transaction::BANK_CHANNELS,
            'ewalletChannels' => Transaction::EWALLET_CHANNELS,
            'deliveryMethods' => Transaction::DELIVERY_METHODS,
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
        $universities = University::orderBy('name')->get();
        $originUniversity = $seller->student?->university ?? University::where('slug', 'undip')->first() ?? $universities->first();
        $buyerUniversity = $user->student?->university ?? $originUniversity;

        $initialRateData = ($originUniversity && $buyerUniversity)
            ? app(CampusShippingRateService::class)->calculateRate($originUniversity, $buyerUniversity, 'jnt')
            : null;

        return view('transactions.checkout', [
            'product' => $product,
            'offer' => $offer,
            'price' => $price,
            'seller' => $seller,
            'universities' => $universities,
            'originUniversity' => $originUniversity,
            'buyerUniversity' => $buyerUniversity,
            'initialRateData' => $initialRateData,
            'paymentMethods' => Transaction::PAYMENT_METHODS,
            'bankChannels' => Transaction::BANK_CHANNELS,
            'ewalletChannels' => Transaction::EWALLET_CHANNELS,
            'deliveryMethods' => Transaction::DELIVERY_METHODS,
            'couriers' => Transaction::COURIERS,
            'courierFee' => (float) ($initialRateData['cost'] ?? ($product->courier_fee ?? 10000)),
            'holdingAccounts' => EscrowPayment::HOLDING_ACCOUNTS,
        ]);
    }

    /**
     * Store a newly created transaction order.
     */
    public function store(CheckoutTransactionRequest $request): RedirectResponse
    {
        $user = $request->user();
        $product = Product::with('user.student.university')->findOrFail($request->validated('product_id'));

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
        $deliveryMethod = $request->validated('delivery_method');
        $useEscrow = $paymentMethod !== Transaction::PAYMENT_METHOD_COD && $request->boolean('use_escrow', true);

        // Resolve origin & destination campus for shipping calculation
        $destinationUniversityId = $request->validated('destination_university_id');
        $originUniversityId = $request->validated('origin_university_id');

        if ($destinationUniversityId && ! $originUniversityId) {
            $originUniversityId = $seller->student?->university_id ?: University::first()?->id;
        }

        $originUniversity = $originUniversityId ? University::find($originUniversityId) : null;
        $destinationUniversity = $destinationUniversityId ? University::find($destinationUniversityId) : null;

        $shippingCost = 0.00;
        $distanceKm = null;
        $estimatedDeliveryDays = null;
        $courierName = $request->validated('courier_name');

        if ($deliveryMethod === Transaction::DELIVERY_METHOD_COURIER) {
            if ($originUniversity && $destinationUniversity) {
                $rateData = app(CampusShippingRateService::class)->calculateRate(
                    $originUniversity,
                    $destinationUniversity,
                    $courierName
                );
                $shippingCost = (float) $rateData['cost'];
                $distanceKm = $rateData['distance_km'];
                $estimatedDeliveryDays = $rateData['etd'];
                $courierName = $rateData['selected_courier'];
            } else {
                $shippingCost = (float) ($product->courier_fee ?? 10000.00);
            }
        }

        $totalAmount = $price + $shippingCost;

        $pickupLocation = null;
        if ($deliveryMethod === Transaction::DELIVERY_METHOD_PICKUP) {
            $pickupLocation = $request->validated('pickup_location')
                ?: ($product->pickup_location ?: ($seller->preferred_pickup_location ?: 'Titik Pengambilan Kampus yang ditentukan penjual'));
        }

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
            'preferred_pickup_location' => $seller->preferred_pickup_location,
        ];

        // For COD, the transaction starts in processing status (waiting for meetup)
        $status = $paymentMethod === Transaction::PAYMENT_METHOD_COD
            ? Transaction::STATUS_PROCESSING
            : Transaction::STATUS_PENDING;

        $transaction = DB::transaction(function () use ($product, $offer, $user, $seller, $price, $paymentMethod, $paymentChannel, $deliveryMethod, $originUniversityId, $destinationUniversityId, $distanceKm, $estimatedDeliveryDays, $shippingCost, $totalAmount, $pickupLocation, $request, $courierName, $paymentDetails, $status, $useEscrow) {
            $trx = Transaction::create([
                'product_id' => $product->id,
                'offer_id' => $offer?->id,
                'buyer_id' => $user->id,
                'seller_id' => $seller->id,
                'amount' => $price,
                'delivery_method' => $deliveryMethod,
                'origin_university_id' => $originUniversityId,
                'destination_university_id' => $destinationUniversityId,
                'distance_km' => $distanceKm,
                'estimated_delivery_days' => $estimatedDeliveryDays,
                'shipping_cost' => $shippingCost,
                'total_amount' => $totalAmount,
                'pickup_location' => $pickupLocation,
                'shipping_recipient_name' => $request->validated('shipping_recipient_name'),
                'shipping_phone' => $request->validated('shipping_phone'),
                'shipping_address' => $request->validated('shipping_address'),
                'courier_name' => $courierName,
                'payment_method' => $paymentMethod,
                'payment_channel' => $paymentChannel,
                'payment_status' => Transaction::PAYMENT_STATUS_PENDING,
                'status' => $status,
                'meeting_location' => $request->validated('meeting_location'),
                'buyer_notes' => $request->validated('buyer_notes'),
                'payment_details' => $paymentDetails,
                'use_escrow' => $useEscrow,
            ]);

            if ($useEscrow) {
                $holdingAccount = EscrowPayment::getHoldingAccount($paymentChannel);
                EscrowPayment::create([
                    'transaction_id' => $trx->id,
                    'amount' => $price,
                    'shipping_cost' => $shippingCost,
                    'platform_fee' => 0.00,
                    'total_amount' => $totalAmount,
                    'status' => EscrowPayment::STATUS_PENDING_DEPOSIT,
                    'holding_channel' => $paymentChannel,
                    'holding_account_number' => $holdingAccount['account_number'],
                    'holding_account_name' => $holdingAccount['account_name'],
                    'seller_payout_details' => [
                        'bank_name' => $seller->bank_name,
                        'bank_name_label' => $seller->bank_name_label,
                        'bank_account_number' => $seller->bank_account_number,
                        'bank_account_name' => $seller->bank_account_name,
                        'ewallet_provider' => $seller->ewallet_provider,
                        'ewallet_provider_label' => $seller->ewallet_provider_label,
                        'ewallet_account_number' => $seller->ewallet_account_number,
                        'ewallet_account_name' => $seller->ewallet_account_name,
                    ],
                ]);
            }

            $product->update(['status' => 'sold']);

            return $trx;
        });

        $message = match ($paymentMethod) {
            Transaction::PAYMENT_METHOD_COD => 'Pesanan COD berhasil dibuat. Silakan atur waktu pertemuan di kampus.',
            Transaction::PAYMENT_METHOD_BANK_TRANSFER => $useEscrow
                ? 'Pesanan berhasil dibuat dengan Proteksi Rekber Kampus. Silakan transfer ke rekening penampung resmi CampusMarket.'
                : 'Pesanan berhasil dibuat. Silakan selesaikan transfer bank ke rekening penjual.',
            Transaction::PAYMENT_METHOD_EWALLET => $useEscrow
                ? 'Pesanan berhasil dibuat dengan Proteksi Rekber Kampus. Silakan transfer saldo ke akun e-wallet resmi CampusMarket.'
                : 'Pesanan berhasil dibuat. Silakan lakukan transfer saldo e-wallet ke nomor penjual.',
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
            'originUniversity',
            'destinationUniversity',
            'offer',
            'escrowPayment.verifier',
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

            if ($transaction->escrowPayment) {
                $transaction->escrowPayment->update([
                    'deposit_proof' => $path,
                    'deposited_at' => now(),
                    'status' => EscrowPayment::STATUS_HELD,
                    'verified_at' => now(),
                ]);
            }
        }

        return back()->with('status', 'Bukti pembayaran berhasil diunggah. Menunggu konfirmasi penerimaan dari penjual.');
    }

    /**
     * Seller inputs courier tracking details and marks item as shipped.
     */
    public function ship(ShipTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $user = $request->user();

        if ($transaction->seller_id !== $user->id) {
            abort(403, 'Hanya penjual yang dapat memperbarui informasi pengiriman barang.');
        }

        if (in_array($transaction->status, [Transaction::STATUS_COMPLETED, Transaction::STATUS_CANCELLED], true)) {
            return back()->with('error', 'Status transaksi saat ini tidak mengizinkan pembaruan pengiriman.');
        }

        $transaction->update([
            'courier_name' => $request->validated('courier_name') ?? $transaction->courier_name,
            'tracking_number' => $request->validated('tracking_number'),
            'seller_notes' => $request->validated('seller_notes') ?? $transaction->seller_notes,
            'shipped_at' => now(),
            'status' => Transaction::STATUS_PROCESSING,
        ]);

        return back()->with('status', 'Informasi pengiriman dan nomor resi berhasil disimpan.');
    }

    /**
     * Buyer or seller marks that the item has been delivered or received.
     */
    public function markDelivered(Request $request, Transaction $transaction): RedirectResponse
    {
        $user = $request->user();

        if ($transaction->buyer_id !== $user->id && $transaction->seller_id !== $user->id) {
            abort(403, 'Anda tidak berhak memperbarui status penerimaan barang.');
        }

        if (in_array($transaction->status, [Transaction::STATUS_COMPLETED, Transaction::STATUS_CANCELLED], true)) {
            return back()->with('error', 'Transaksi sudah selesai atau dibatalkan.');
        }

        $transaction->update([
            'delivered_at' => now(),
        ]);

        return back()->with('status', 'Status pesanan berhasil diperbarui: barang telah diterima.');
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
            'delivered_at' => $transaction->delivered_at ?? now(),
        ]);

        if ($transaction->escrowPayment && $transaction->escrowPayment->status === EscrowPayment::STATUS_HELD) {
            $transaction->escrowPayment->releaseToSeller('Dikonfirmasi selesai oleh penjual.');
        }

        return back()->with('status', 'Pembayaran berhasil diverifikasi. Transaksi jual-beli telah selesai.');
    }

    /**
     * Buyer confirms item received and releases escrow funds to seller.
     */
    public function releaseEscrow(Request $request, Transaction $transaction): RedirectResponse
    {
        $user = $request->user();

        if ($transaction->buyer_id !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Hanya pembeli yang berhak mengonfirmasi dan melepas dana rekening bersama.');
        }

        if (! $transaction->escrowPayment) {
            return back()->with('error', 'Transaksi ini tidak menggunakan sistem proteksi rekening bersama.');
        }

        if ($transaction->escrowPayment->status !== EscrowPayment::STATUS_HELD) {
            return back()->with('error', 'Dana rekber belum berstatus diamankan atau sudah dicairkan sebelumnya.');
        }

        $transaction->escrowPayment->releaseToSeller('Dikonfirmasi selesai dan dilepas langsung oleh pembeli.');

        return back()->with('status', 'Terima kasih! Barang telah Anda konfirmasi dan dana rekber telah dicairkan ke rekening penjual.');
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

            if ($transaction->escrowPayment) {
                if ($transaction->escrowPayment->status === EscrowPayment::STATUS_HELD) {
                    $transaction->escrowPayment->refundToBuyer('Transaksi dibatalkan.');
                } else {
                    $transaction->escrowPayment->update([
                        'status' => EscrowPayment::STATUS_REFUNDED,
                        'refunded_at' => now(),
                        'refund_reason' => 'Transaksi dibatalkan sebelum setoran.',
                    ]);
                }
            }

            // Reactivate the product so it is available again in the marketplace
            $transaction->product->update(['status' => 'active']);
        });

        return back()->with('status', 'Transaksi berhasil dibatalkan dan produk dikembalikan ke etalase.');
    }
}
