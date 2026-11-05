<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointTransaction;
use App\Models\RewardItem;
use App\Models\RewardRedemption;
use App\Models\User;
use App\Services\RewardPointService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RewardController extends Controller
{
    /**
     * Display reward system dashboard and reward catalog management.
     */
    public function index(): View
    {
        $totalCirculating = (int) User::sum('points_balance');
        $totalEarned = (int) PointTransaction::where('amount', '>', 0)->sum('amount');
        $totalSpent = (int) abs(PointTransaction::where('amount', '<', 0)->sum('amount'));
        $usersWithPointsCount = User::where('points_balance', '>', 0)->count();
        $totalRedemptionsCount = RewardRedemption::count();

        $rewardItems = RewardItem::query()
            ->withCount('redemptions')
            ->latest()
            ->paginate(10);

        return view('admin.rewards.index', [
            'rewardItems' => $rewardItems,
            'totalCirculating' => $totalCirculating,
            'totalEarned' => $totalEarned,
            'totalSpent' => $totalSpent,
            'usersWithPointsCount' => $usersWithPointsCount,
            'totalRedemptionsCount' => $totalRedemptionsCount,
        ]);
    }

    /**
     * Show form to create a new reward item.
     */
    public function create(): View
    {
        return view('admin.rewards.create');
    }

    /**
     * Store a newly created reward item.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'points_cost' => ['required', 'integer', 'min:1'],
            'reward_type' => ['required', 'string', 'in:discount_fixed,free_shipping,merchandise,badge,other'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:-1'],
            'validity_days' => ['required', 'integer', 'min:0', 'max:365'],
            'icon' => ['nullable', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['discount_amount'] = $validated['discount_amount'] ?? 0;

        RewardItem::create($validated);

        return redirect()
            ->route('admin.rewards.index')
            ->with('status', 'Item reward baru berhasil ditambahkan ke katalog mahasiswa.');
    }

    /**
     * Show form to edit an existing reward item.
     */
    public function edit(RewardItem $rewardItem): View
    {
        return view('admin.rewards.edit', [
            'rewardItem' => $rewardItem,
        ]);
    }

    /**
     * Update an existing reward item.
     */
    public function update(Request $request, RewardItem $rewardItem): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'points_cost' => ['required', 'integer', 'min:1'],
            'reward_type' => ['required', 'string', 'in:discount_fixed,free_shipping,merchandise,badge,other'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:-1'],
            'validity_days' => ['required', 'integer', 'min:0', 'max:365'],
            'icon' => ['nullable', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['discount_amount'] = $validated['discount_amount'] ?? 0;

        $rewardItem->update($validated);

        return redirect()
            ->route('admin.rewards.index')
            ->with('status', "Item reward '{$rewardItem->title}' berhasil diperbarui.");
    }

    /**
     * Toggle active status of a reward item.
     */
    public function toggleStatus(RewardItem $rewardItem): RedirectResponse
    {
        $rewardItem->update([
            'is_active' => ! $rewardItem->is_active,
        ]);

        $statusText = $rewardItem->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('status', "Item reward '{$rewardItem->title}' berhasil {$statusText}.");
    }

    /**
     * Delete a reward item.
     */
    public function destroy(RewardItem $rewardItem): RedirectResponse
    {
        $title = $rewardItem->title;
        $rewardItem->delete();

        return redirect()
            ->route('admin.rewards.index')
            ->with('status', "Item reward '{$title}' berhasil dihapus dari sistem.");
    }

    /**
     * Manage student points and list active users.
     */
    public function users(Request $request): View
    {
        $query = User::query()->with(['student.university']);

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($minPoints = $request->input('min_points')) {
            $query->where('points_balance', '>=', (int) $minPoints);
        }

        $users = $query->orderByDesc('points_balance')->paginate(15)->withQueryString();

        return view('admin.rewards.users', [
            'users' => $users,
            'search' => $request->input('q', ''),
        ]);
    }

    /**
     * Adjust user points manually (grant bonus or deduct points).
     */
    public function adjustPoints(Request $request, User $user, RewardPointService $service): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:add,deduct'],
            'points' => ['required', 'integer', 'min:1', 'max:50000'],
            'reason' => ['required', 'string', 'min:3', 'max:255'],
        ]);

        $admin = $request->user();
        $action = $validated['action'];
        $points = (int) $validated['points'];
        $reason = $validated['reason'];

        try {
            if ($action === 'add') {
                $service->awardPoints(
                    $user,
                    $points,
                    PointTransaction::TYPE_ADMIN_ADJUSTMENT,
                    "Bonus Admin: {$reason}",
                    null,
                    $admin
                );
                $message = "Berhasil menambahkan {$points} poin untuk mahasiswa {$user->name}.";
            } else {
                $service->deductPoints(
                    $user,
                    $points,
                    PointTransaction::TYPE_ADMIN_ADJUSTMENT,
                    "Pemotongan Admin: {$reason}",
                    null,
                    $admin
                );
                $message = "Berhasil memotong {$points} poin dari saldo mahasiswa {$user->name}.";
            }

            return back()->with('status', $message);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * List all reward redemptions and process fulfillment.
     */
    public function redemptions(Request $request): View
    {
        $query = RewardRedemption::query()->with(['user.student.university', 'rewardItem']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $redemptions = $query->latest()->paginate(15)->withQueryString();

        return view('admin.rewards.redemptions', [
            'redemptions' => $redemptions,
            'statusFilter' => $request->input('status', ''),
        ]);
    }

    /**
     * Mark a redemption as fulfilled (e.g. merchandise handed over or voucher redeemed).
     */
    public function markRedemptionFulfilled(Request $request, RewardRedemption $redemption): RedirectResponse
    {
        $redemption->update([
            'status' => RewardRedemption::STATUS_FULFILLED,
            'used_at' => now(),
            'admin_notes' => $request->input('notes', 'Klaim reward telah diproses dan diserahkan oleh administrator.'),
        ]);

        return back()->with('status', "Klaim reward untuk kode {$redemption->voucher_code} telah ditandai terpenuhi.");
    }

    /**
     * View point transactions audit ledger.
     */
    public function history(Request $request): View
    {
        $query = PointTransaction::query()->with(['user', 'admin']);

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $transactions = $query->latest()->paginate(20)->withQueryString();

        return view('admin.rewards.history', [
            'transactions' => $transactions,
            'typeFilter' => $request->input('type', ''),
        ]);
    }
}
