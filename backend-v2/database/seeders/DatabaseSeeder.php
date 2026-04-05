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
        // PLANS (drives the pricing page)
        // =============================================

        DB::table('plans')->insert([
            'name' => 'Basic',
            'description' => 'For single-outlet brands getting started with smart recycling',
            'price_monthly' => 199.00,
            'price_yearly' => 1990.00,
            'features' => json_encode([]),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('plans')->insert([
            'name' => 'Pro',
            'description' => 'For brands scaling across multiple outlets',
            'price_monthly' => 499.00,
            'price_yearly' => 4990.00,
            'features' => json_encode([]),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('plans')->insert([
            'name' => 'Custom',
            'description' => 'For large deployments, government programs, or unique requirements',
            'price_monthly' => 0.00,
            'price_yearly' => 0.00,
            'features' => json_encode([]),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // =============================================
        // USERS (one per role, for dashboard access)
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

        DB::table('users')->insert([
            'name' => 'Sarah Lee',
            'email' => 'brand@mobius.my',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'phone' => '0171112222',
            'roles' => json_encode(['brand_owner']),
            'points_balance' => 0,
            'current_streak' => 0,
            'longest_streak' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'name' => 'Jenny Wong',
            'email' => 'store@mobius.my',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'phone' => '0171113333',
            'roles' => json_encode(['store_owner']),
            'points_balance' => 0,
            'current_streak' => 0,
            'longest_streak' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'name' => 'Kumar Rajan',
            'email' => 'collector@mobius.my',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'phone' => '0195556666',
            'roles' => json_encode(['collector']),
            'points_balance' => 0,
            'current_streak' => 0,
            'longest_streak' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'name' => 'Mei Ling',
            'email' => 'public@mobius.my',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'phone' => '0167778888',
            'roles' => json_encode(['public_user']),
            'points_balance' => 0,
            'current_streak' => 0,
            'longest_streak' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
