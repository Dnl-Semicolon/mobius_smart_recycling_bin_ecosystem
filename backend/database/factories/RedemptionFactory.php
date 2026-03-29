<?php

namespace Database\Factories;

use App\Enums\RedemptionStatus;
use App\Models\Reward;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Redemption>
 */
class RedemptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'reward_id' => Reward::factory(),
            'points_spent' => 100,
            'voucher_code' => strtoupper(Str::random(8)),
            'status' => RedemptionStatus::Pending,
            'redeemed_at' => now(),
        ];
    }

    public function fulfilled(): static
    {
        return $this->state(fn () => [
            'status' => RedemptionStatus::Fulfilled,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status' => RedemptionStatus::Expired,
            'expires_at' => now()->subDay(),
        ]);
    }
}
