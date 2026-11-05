<?php

namespace App\Services;

use App\Models\PointTransaction;
use App\Models\RewardItem;
use App\Models\RewardRedemption;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class RewardPointService
{
    /**
     * Default point awards for actions.
     */
    public const POINTS_DAILY_CHECKIN = 10;

    public const POINTS_TRANSACTION_COMPLETED = 40;

    public const POINTS_REVIEW_SUBMITTED = 15;

    public const POINTS_PRODUCT_LISTED = 20;

    public const POINTS_WANTED_ACCEPTED = 25;

    /**
     * Award points to a user and record the ledger transaction.
     */
    public function awardPoints(
        User $user,
        int $points,
        string $type,
        string $description,
        ?Model $reference = null,
        ?User $admin = null
    ): PointTransaction {
        if ($points <= 0) {
            throw new InvalidArgumentException('Jumlah poin reward harus lebih besar dari nol.');
        }

        return DB::transaction(function () use ($user, $points, $type, $description, $reference, $admin) {
            $user->increment('points_balance', $points);
            $user->increment('points_lifetime', $points);

            return PointTransaction::create([
                'user_id' => $user->id,
                'amount' => $points,
                'type' => $type,
                'description' => $description,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->getKey(),
                'created_by_admin_id' => $admin?->id,
            ]);
        });
    }

    /**
     * Deduct points from a user and record the ledger transaction.
     */
    public function deductPoints(
        User $user,
        int $points,
        string $type,
        string $description,
        ?Model $reference = null,
        ?User $admin = null
    ): PointTransaction {
        if ($points <= 0) {
            throw new InvalidArgumentException('Jumlah poin yang dipotong harus lebih besar dari nol.');
        }

        if ($user->points_balance < $points) {
            throw new InvalidArgumentException("Saldo poin tidak mencukupi (Tersedia: {$user->points_balance}, Dibutuhkan: {$points}).");
        }

        return DB::transaction(function () use ($user, $points, $type, $description, $reference, $admin) {
            $user->decrement('points_balance', $points);

            return PointTransaction::create([
                'user_id' => $user->id,
                'amount' => -$points,
                'type' => $type,
                'description' => $description,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->getKey(),
                'created_by_admin_id' => $admin?->id,
            ]);
        });
    }

    /**
     * Process daily check-in for an active student.
     */
    public function dailyCheckIn(User $user): PointTransaction
    {
        if (! $user->canCheckInToday()) {
            throw new InvalidArgumentException('Anda sudah melakukan check-in hari ini. Silakan kembali besok!');
        }

        return DB::transaction(function () use ($user) {
            $user->update(['last_check_in_at' => now()]);

            return $this->awardPoints(
                $user,
                self::POINTS_DAILY_CHECKIN,
                PointTransaction::TYPE_DAILY_CHECK_IN,
                'Daily Check-in Mahasiswa Aktif (+10 Poin)'
            );
        });
    }

    /**
     * Redeem a reward item using student points.
     */
    public function redeemReward(User $user, RewardItem $reward): RewardRedemption
    {
        if (! $reward->isAvailable()) {
            throw new InvalidArgumentException('Reward ini sedang tidak tersedia atau stok telah habis.');
        }

        if ($user->points_balance < $reward->points_cost) {
            throw new InvalidArgumentException("Saldo poin Anda ({$user->points_balance}) tidak mencukupi untuk menukar reward ini ({$reward->points_cost} poin).");
        }

        return DB::transaction(function () use ($user, $reward) {
            // Deduct points
            $this->deductPoints(
                $user,
                $reward->points_cost,
                PointTransaction::TYPE_REWARD_REDEMPTION,
                "Penukaran Reward: {$reward->title}",
                $reward
            );

            // Decrement stock if finite
            if (! $reward->isUnlimitedStock()) {
                $reward->decrement('stock');
            }

            // Generate unique voucher code
            $voucherCode = 'CMP-'.strtoupper(Str::random(4)).'-'.mt_rand(1000, 9999);
            while (RewardRedemption::where('voucher_code', $voucherCode)->exists()) {
                $voucherCode = 'CMP-'.strtoupper(Str::random(4)).'-'.mt_rand(1000, 9999);
            }

            return RewardRedemption::create([
                'user_id' => $user->id,
                'reward_item_id' => $reward->id,
                'points_spent' => $reward->points_cost,
                'voucher_code' => $voucherCode,
                'status' => RewardRedemption::STATUS_ACTIVE,
                'expires_at' => $reward->validity_days > 0 ? now()->addDays($reward->validity_days) : null,
            ]);
        });
    }
}
