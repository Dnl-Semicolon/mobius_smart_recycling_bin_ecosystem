<?php

namespace App\Services;

use App\Enums\RedemptionStatus;
use App\Models\Redemption;
use App\Models\Reward;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RewardService
{
    /**
     * Redeem a reward for a user. Atomic: deducts points, decrements stock, creates redemption.
     *
     * @return array{success: bool, redemption?: Redemption, error?: string}
     */
    public function redeem(User $user, Reward $reward): array
    {
        if (! $reward->isAvailable()) {
            return ['success' => false, 'error' => 'This reward is no longer available.'];
        }

        if ($user->points_balance < $reward->points_cost) {
            return ['success' => false, 'error' => 'Insufficient points. You need '.$reward->points_cost.' points but have '.$user->points_balance.'.'];
        }

        return DB::transaction(function () use ($user, $reward) {
            // Lock the reward row to prevent overselling
            $reward = Reward::lockForUpdate()->find($reward->id);

            if (! $reward->isAvailable()) {
                return ['success' => false, 'error' => 'This reward just became unavailable.'];
            }

            // Deduct user points
            $user->decrement('points_balance', $reward->points_cost);

            // Decrement stock if limited
            if ($reward->stock !== null) {
                $reward->decrement('stock');
            }

            // Deduct from brand budget if brand-sponsored
            if ($reward->brand_id && $reward->brand) {
                $reward->brand->deductBudget($reward->points_cost);
            }

            // Create redemption record
            $redemption = Redemption::create([
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'points_spent' => $reward->points_cost,
                'voucher_code' => $this->generateVoucherCode(),
                'status' => RedemptionStatus::Pending,
                'redeemed_at' => now(),
                'expires_at' => now()->addDays(30),
            ]);

            // Record as spent transaction
            $user->recyclingTransactions()->create([
                'points_earned' => -$reward->points_cost,
                'type' => 'spent',
                'description' => "Redeemed: {$reward->name}",
            ]);

            return ['success' => true, 'redemption' => $redemption];
        });
    }

    private function generateVoucherCode(): string
    {
        do {
            $code = 'MBX-'.strtoupper(Str::random(8));
        } while (Redemption::where('voucher_code', $code)->exists());

        return $code;
    }
}
