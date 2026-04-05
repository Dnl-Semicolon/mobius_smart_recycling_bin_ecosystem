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

        // =============================================
        // LEADS (submitted via landing page)
        // =============================================

        DB::table('registration_requests')->insert([
            'selected_plan_id' => 1,
            'company_name' => 'Berjaya Starbucks Coffee Company Sdn Bhd',
            'contact_name' => 'Brand Owner Name',
            'contact_email' => 'brand1@brand.com',
            'contact_phone' => '01124120654',
            'type' => 'beverage_company',
            'status' => 'pending',
            'created_at' => '2026-04-05 20:06:01',
            'updated_at' => '2026-04-05 20:06:01',
        ]);

        DB::table('registration_requests')->insert([
            'selected_plan_id' => 2,
            'company_name' => 'Zuspresso (M) Sdn Bhd',
            'contact_name' => 'Brand Owner 2',
            'contact_email' => 'brand2@brandtwo.com',
            'contact_phone' => '01124120655',
            'type' => 'beverage_company',
            'status' => 'pending',
            'created_at' => '2026-04-05 20:06:55',
            'updated_at' => '2026-04-05 20:06:55',
        ]);
    }
}
