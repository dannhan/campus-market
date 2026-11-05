<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EscrowPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EscrowController extends Controller
{
    /**
     * Display a listing of all escrow payments and holdings.
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status');

        $query = EscrowPayment::query()
            ->with([
                'transaction.product.images',
                'transaction.buyer.student.university',
                'transaction.seller.student.university',
                'verifier',
            ])
            ->latest();

        if ($statusFilter && in_array($statusFilter, EscrowPayment::STATUSES, true)) {
            $query->where('status', $statusFilter);
        }

        // Summary statistics
        $totalHeld = (float) EscrowPayment::where('status', EscrowPayment::STATUS_HELD)->sum('total_amount');
        $totalReleased = (float) EscrowPayment::where('status', EscrowPayment::STATUS_RELEASED)->sum('total_amount');
        $totalRefunded = (float) EscrowPayment::where('status', EscrowPayment::STATUS_REFUNDED)->sum('total_amount');
        $pendingCount = EscrowPayment::where('status', EscrowPayment::STATUS_PENDING_DEPOSIT)->count();
        $heldCount = EscrowPayment::where('status', EscrowPayment::STATUS_HELD)->count();
        $disputedCount = EscrowPayment::where('status', EscrowPayment::STATUS_DISPUTED)->count();

        return view('admin.escrows.index', [
            'escrows' => $query->paginate(15)->withQueryString(),
            'statusFilter' => $statusFilter,
            'totalHeld' => $totalHeld,
            'totalReleased' => $totalReleased,
            'totalRefunded' => $totalRefunded,
            'pendingCount' => $pendingCount,
            'heldCount' => $heldCount,
            'disputedCount' => $disputedCount,
            'statuses' => [
                EscrowPayment::STATUS_PENDING_DEPOSIT => 'Menunggu Pembeli Transfer',
                EscrowPayment::STATUS_HELD => 'Dana Diamankan di Rekber',
                EscrowPayment::STATUS_RELEASED => 'Dana Telah Dicairkan ke Penjual',
                EscrowPayment::STATUS_REFUNDED => 'Dana Dikembalikan ke Pembeli',
                EscrowPayment::STATUS_DISPUTED => 'Dana Dibekukan (Dispute)',
            ],
        ]);
    }

    /**
     * Admin manually verifies buyer's deposit proof and marks escrow funds as held.
     */
    public function verify(Request $request, EscrowPayment $escrow): RedirectResponse
    {
        if ($escrow->status === EscrowPayment::STATUS_HELD) {
            return back()->with('error', 'Dana pada transaksi ini sudah diverifikasi dan berstatus aman di rekening bersama.');
        }

        $escrow->markHeld($request->user());

        return back()->with('status', "Setoran rekber {$escrow->escrow_code} berhasil diverifikasi. Dana saat ini diamankan di rekening penampung resmi.");
    }

    /**
     * Admin manually releases held escrow funds to seller's bank/e-wallet account.
     */
    public function release(Request $request, EscrowPayment $escrow): RedirectResponse
    {
        if (in_array($escrow->status, [EscrowPayment::STATUS_RELEASED, EscrowPayment::STATUS_REFUNDED], true)) {
            return back()->with('error', 'Status escrow saat ini tidak dapat dicairkan kembali.');
        }

        $notes = $request->input('notes', 'Pencairan dana rekber diproses oleh Admin CampusMarket.');
        $escrow->releaseToSeller($notes);

        return back()->with('status', "Dana sebesar {$escrow->formatted_total_amount} untuk {$escrow->escrow_code} berhasil dicairkan ke rekening penjual.");
    }

    /**
     * Admin refunds escrow funds to buyer.
     */
    public function refund(Request $request, EscrowPayment $escrow): RedirectResponse
    {
        if (in_array($escrow->status, [EscrowPayment::STATUS_RELEASED, EscrowPayment::STATUS_REFUNDED], true)) {
            return back()->with('error', 'Status escrow saat ini tidak dapat di-refund.');
        }

        $reason = $request->input('reason', 'Pengembalian dana diproses oleh Admin CampusMarket atas persetujuan pembatalan/sengketa.');
        $escrow->refundToBuyer($reason);

        return back()->with('status', "Dana sebesar {$escrow->formatted_total_amount} untuk {$escrow->escrow_code} berhasil dikembalikan ke pembeli.");
    }
}
