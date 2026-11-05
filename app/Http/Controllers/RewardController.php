<?php

namespace App\Http\Controllers;

use App\Models\PointTransaction;
use App\Models\RewardItem;
use App\Models\RewardRedemption;
use App\Services\RewardPointService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class RewardController extends Controller
{
    /**
     * Display student reward points, tier progress, and available catalog items.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $rewardItems = RewardItem::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('stock', -1)->orWhere('stock', '>', 0);
            })
            ->orderBy('points_cost')
            ->get();

        $activeRedemptions = RewardRedemption::query()
            ->where('user_id', $user->id)
            ->with('rewardItem')
            ->latest()
            ->take(6)
            ->get();

        $recentTransactions = PointTransaction::query()
            ->where('user_id', $user->id)
            ->latest()
            ->take(6)
            ->get();

        return view('rewards.index', [
            'user' => $user,
            'rewardItems' => $rewardItems,
            'activeRedemptions' => $activeRedemptions,
            'recentTransactions' => $recentTransactions,
            'tier' => $user->reward_tier,
        ]);
    }

    /**
     * Display student point transaction ledger history.
     */
    public function history(Request $request): View
    {
        $user = $request->user();

        $transactions = PointTransaction::query()
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(15);

        return view('rewards.history', [
            'user' => $user,
            'transactions' => $transactions,
            'tier' => $user->reward_tier,
        ]);
    }

    /**
     * Process daily check-in to earn active student points.
     */
    public function checkIn(Request $request, RewardPointService $service): RedirectResponse
    {
        $user = $request->user();

        try {
            $service->dailyCheckIn($user);

            return back()->with('status', 'Selamat! Anda memperoleh +10 Poin Mahasiswa Aktif dari check-in hari ini 🎉');
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Redeem points for a reward item.
     */
    public function redeem(Request $request, RewardItem $rewardItem, RewardPointService $service): RedirectResponse
    {
        $user = $request->user();

        try {
            $redemption = $service->redeemReward($user, $rewardItem);

            return back()->with('status', "Penukaran berhasil! Anda mendapatkan voucher '{$rewardItem->title}'. Kode: {$redemption->voucher_code}");
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
