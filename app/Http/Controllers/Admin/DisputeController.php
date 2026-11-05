<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ResolveDisputeRequest;
use App\Models\Dispute;
use App\Models\EscrowPayment;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DisputeController extends Controller
{
    /**
     * Display a listing of all transaction disputes on the platform.
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status');

        $query = Dispute::query()
            ->with(['product', 'reporter.student.university', 'respondent.student.university', 'admin'])
            ->latest();

        if ($statusFilter && in_array($statusFilter, Dispute::STATUSES, true)) {
            $query->where('status', $statusFilter);
        }

        $disputes = $query->paginate(15)->withQueryString();

        $openCount = Dispute::where('status', Dispute::STATUS_OPEN)->count();
        $underReviewCount = Dispute::where('status', Dispute::STATUS_UNDER_REVIEW)->count();
        $resolvedCount = Dispute::where('status', Dispute::STATUS_RESOLVED)->count();
        $closedCount = Dispute::where('status', Dispute::STATUS_CLOSED)->count();

        return view('admin.disputes.index', [
            'disputes' => $disputes,
            'currentStatus' => $statusFilter,
            'openCount' => $openCount,
            'underReviewCount' => $underReviewCount,
            'resolvedCount' => $resolvedCount,
            'closedCount' => $closedCount,
        ]);
    }

    /**
     * Show the mediation case file and decision form.
     */
    public function show(Dispute $dispute): View
    {
        $dispute->load([
            'product.user.student.university',
            'product.category',
            'offer',
            'reporter.student.university',
            'respondent.student.university',
            'admin',
        ]);

        return view('admin.disputes.show', [
            'dispute' => $dispute,
            'statuses' => [
                Dispute::STATUS_UNDER_REVIEW => 'Sedang Ditinjau Admin (Proses Mediasi)',
                Dispute::STATUS_RESOLVED => 'Mediasi Selesai (Kesepakatan Tercapai)',
                Dispute::STATUS_CLOSED => 'Tutup Kasus (Sengketa Dihentikan)',
            ],
            'decisions' => Dispute::DECISIONS,
        ]);
    }

    /**
     * Submit an official administrative mediation decision on the dispute.
     */
    public function update(ResolveDisputeRequest $request, Dispute $dispute): RedirectResponse
    {
        $validated = $request->validated();
        $newStatus = $validated['status'];

        $resolvedAt = in_array($newStatus, [Dispute::STATUS_RESOLVED, Dispute::STATUS_CLOSED], true)
            ? now()
            : null;

        $dispute->update([
            'status' => $newStatus,
            'admin_id' => $request->user()->id,
            'admin_decision' => $validated['admin_decision'] ?? null,
            'admin_notes' => $validated['admin_notes'],
            'resolved_at' => $resolvedAt,
        ]);

        $transaction = Transaction::where('product_id', $dispute->product_id)->latest()->first();
        if ($transaction?->escrowPayment && $transaction->escrowPayment->status === EscrowPayment::STATUS_DISPUTED) {
            $decision = $validated['admin_decision'] ?? null;
            if ($decision === Dispute::DECISION_FAVOR_BUYER) {
                $transaction->escrowPayment->refundToBuyer("Penyelesaian mediasi sengketa admin: memenangkan pembeli. Catatan: {$validated['admin_notes']}");
            } elseif ($decision === Dispute::DECISION_FAVOR_SELLER) {
                $transaction->escrowPayment->releaseToSeller("Penyelesaian mediasi sengketa admin: memenangkan penjual. Catatan: {$validated['admin_notes']}");
            }
        }

        return redirect()
            ->route('admin.disputes.show', $dispute)
            ->with('status', 'Keputusan dan catatan mediasi berhasil disimpan serta dikirimkan ke pihak terkait.');
    }
}
