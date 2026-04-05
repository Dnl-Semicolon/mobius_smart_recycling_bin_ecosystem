<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // =============================================
        // PLANS (prices match Stripe sandbox)
        // =============================================

        DB::table('plans')->insert([
            'name' => 'Basic',
            'description' => 'For single-outlet brands getting started with smart recycling',
            'price_monthly' => 49.00,
            'price_yearly' => 490.00,
            'bin_limit' => 3,
            'outlet_limit' => 3,
            'staff_limit' => 3,
            'api_access' => false,
            'features' => json_encode(['analytics' => 'basic', 'support' => 'email']),
            'stripe_price_id' => 'price_1THxnGPdFnIiZZ0SiAdiATkW',
            'stripe_price_yearly_id' => null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('plans')->insert([
            'name' => 'Premium',
            'description' => 'For brands scaling across multiple outlets',
            'price_monthly' => 149.00,
            'price_yearly' => 1490.00,
            'bin_limit' => 15,
            'outlet_limit' => 15,
            'staff_limit' => 10,
            'api_access' => false,
            'features' => json_encode(['analytics' => 'advanced', 'support' => 'priority']),
            'stripe_price_id' => 'price_1THxnaPdFnIiZZ0SLL6GxwWs',
            'stripe_price_yearly_id' => null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('plans')->insert([
            'name' => 'Custom',
            'description' => 'For large deployments, government programs, or unique requirements',
            'price_monthly' => 0.00,
            'price_yearly' => 0.00,
            'bin_limit' => null,
            'outlet_limit' => null,
            'staff_limit' => null,
            'api_access' => true,
            'features' => json_encode(['analytics' => 'full', 'support' => 'dedicated']),
            'stripe_price_id' => null,
            'stripe_price_yearly_id' => null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // =============================================
        // USERS (admin only — other roles created via lead conversion)
        // =============================================

        DB::table('users')->insert([
            'name' => 'Daniel Tan',
            'email' => 'admin@mobius.my',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'phone' => '0121234567',
            'roles' => json_encode(['admin']),
            'points_balance' => 0,
            'current_streak' => 0,
            'longest_streak' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
