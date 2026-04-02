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
        // ORGANIZATIONS
        // =============================================

        // Org 1 — Starbucks Malaysia
        DB::table('organizations')->insert([
            'name' => 'Starbucks Malaysia Sdn Bhd',
            'type' => 'beverage_company',
            'description' => 'Licensed operator of Starbucks in Malaysia',
            'logo_path' => 'organizations/starbucks-my.png',
            'website' => 'https://www.starbucks.com.my',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Org 2 — Mixue Malaysia
        DB::table('organizations')->insert([
            'name' => 'Mixue Malaysia Sdn Bhd',
            'type' => 'beverage_company',
            'description' => 'Licensed operator of Mixue in Malaysia',
            'logo_path' => 'organizations/mixue-my.png',
            'website' => 'https://www.mixue.com',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // =============================================
        // PLANS
        // =============================================

        // Plan 1 — Basic
        DB::table('plans')->insert([
            'name' => 'Basic',
            'description' => 'For small businesses getting started with smart recycling',
            'price_monthly' => 299.00,
            'price_yearly' => 2990.00,
            'features' => json_encode([
                'bin_limit' => 5,
                'analytics' => 'basic',
                'support' => 'email',
            ]),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Plan 2 — Pro
        DB::table('plans')->insert([
            'name' => 'Pro',
            'description' => 'For growing brands with multiple outlets',
            'price_monthly' => 599.00,
            'price_yearly' => 5990.00,
            'features' => json_encode([
                'bin_limit' => 20,
                'analytics' => 'advanced',
                'support' => 'priority',
                'brand_detection' => true,
            ]),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Plan 3 — Enterprise
        DB::table('plans')->insert([
            'name' => 'Enterprise',
            'description' => 'For large organizations with nationwide deployments',
            'price_monthly' => 999.00,
            'price_yearly' => 9990.00,
            'features' => json_encode([
                'bin_limit' => 100,
                'analytics' => 'full',
                'support' => 'dedicated',
                'brand_detection' => true,
                'api_access' => true,
            ]),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // =============================================
        // USERS
        // =============================================

        // User 1 — Admin (no org)
        DB::table('users')->insert([
            'organization_id' => null,
            'name' => 'Daniel Tan',
            'email' => 'admin@mobius.my',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'phone' => '0121234567',
            'phone_verified_at' => now(),
            'profile_photo_path' => 'profiles/daniel.jpg',
            'roles' => json_encode(['admin']),
            'points_balance' => 0,
            'current_streak' => 0,
            'longest_streak' => 0,
            'last_recycled_at' => now(),
            'remember_token' => '',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // User 2 — Brand Owner, Starbucks Malaysia
        DB::table('users')->insert([
            'organization_id' => 1,
            'name' => 'Sarah Lee',
            'email' => 'sarah@starbucks.com.my',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'phone' => '0171112222',
            'phone_verified_at' => now(),
            'profile_photo_path' => 'profiles/sarah.jpg',
            'roles' => json_encode(['brand_owner']),
            'points_balance' => 0,
            'current_streak' => 0,
            'longest_streak' => 0,
            'last_recycled_at' => now(),
            'remember_token' => '',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // User 3 — Store Owner, Starbucks Gurney
        DB::table('users')->insert([
            'organization_id' => 1,
            'name' => 'Jenny Wong',
            'email' => 'jenny@starbucks.com.my',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'phone' => '0171113333',
            'phone_verified_at' => now(),
            'profile_photo_path' => 'profiles/jenny.jpg',
            'roles' => json_encode(['store_owner']),
            'points_balance' => 0,
            'current_streak' => 0,
            'longest_streak' => 0,
            'last_recycled_at' => now(),
            'remember_token' => '',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // User 4 — Store Owner, Starbucks Queensbay
        DB::table('users')->insert([
            'organization_id' => 1,
            'name' => 'Lim Wei Ming',
            'email' => 'weiming@starbucks.com.my',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'phone' => '0171114444',
            'phone_verified_at' => now(),
            'profile_photo_path' => 'profiles/weiming.jpg',
            'roles' => json_encode(['store_owner']),
            'points_balance' => 0,
            'current_streak' => 0,
            'longest_streak' => 0,
            'last_recycled_at' => now(),
            'remember_token' => '',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // User 5 — Brand Owner, Mixue Malaysia
        DB::table('users')->insert([
            'organization_id' => 2,
            'name' => 'Ahmad Rizal',
            'email' => 'rizal@mixue.com.my',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'phone' => '0183334444',
            'phone_verified_at' => now(),
            'profile_photo_path' => 'profiles/ahmad.jpg',
            'roles' => json_encode(['brand_owner']),
            'points_balance' => 0,
            'current_streak' => 0,
            'longest_streak' => 0,
            'last_recycled_at' => now(),
            'remember_token' => '',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // User 6 — Store Owner, Mixue Komtar
        DB::table('users')->insert([
            'organization_id' => 2,
            'name' => 'Nurul Aisyah',
            'email' => 'nurul@mixue.com.my',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'phone' => '0183335555',
            'phone_verified_at' => now(),
            'profile_photo_path' => 'profiles/nurul.jpg',
            'roles' => json_encode(['store_owner']),
            'points_balance' => 0,
            'current_streak' => 0,
            'longest_streak' => 0,
            'last_recycled_at' => now(),
            'remember_token' => '',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // User 7 — Collector (no org for now)
        DB::table('users')->insert([
            'organization_id' => null,
            'name' => 'Kumar Rajan',
            'email' => 'kumar@mobius.my',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'phone' => '0195556666',
            'phone_verified_at' => now(),
            'profile_photo_path' => 'profiles/kumar.jpg',
            'roles' => json_encode(['collector']),
            'points_balance' => 0,
            'current_streak' => 0,
            'longest_streak' => 0,
            'last_recycled_at' => now(),
            'remember_token' => '',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // User 8 — Public User (recycler)
        DB::table('users')->insert([
            'organization_id' => null,
            'name' => 'Mei Ling',
            'email' => 'meiling@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'phone' => '0167778888',
            'phone_verified_at' => now(),
            'profile_photo_path' => 'profiles/meiling.jpg',
            'roles' => json_encode(['public_user']),
            'points_balance' => 0,
            'current_streak' => 0,
            'longest_streak' => 0,
            'last_recycled_at' => now(),
            'remember_token' => '',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // =============================================
        // SUBSCRIPTIONS
        // =============================================

        // Starbucks Malaysia → Pro plan
        DB::table('subscriptions')->insert([
            'organization_id' => 1,
            'plan_id' => 2,
            'status' => 'active',
            'starts_at' => now()->startOfYear(),
            'ends_at' => now()->endOfYear(),
            'renews_at' => now()->endOfYear()->subMonth(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Mixue Malaysia → Basic plan
        DB::table('subscriptions')->insert([
            'organization_id' => 2,
            'plan_id' => 1,
            'status' => 'active',
            'starts_at' => now()->startOfYear(),
            'ends_at' => now()->endOfYear(),
            'renews_at' => now()->endOfYear()->subMonth(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // =============================================
        // PAYMENTS
        // =============================================

        // Starbucks Malaysia — Pro plan payment
        DB::table('payments')->insert([
            'organization_id' => 1,
            'subscription_id' => 1,
            'amount' => 5990.00,
            'currency' => 'MYR',
            'method' => 'bank_transfer',
            'status' => 'completed',
            'reference_number' => 'PAY-2026-0001',
            'paid_at' => now()->startOfYear(),
            'created_at' => now()->startOfYear(),
            'updated_at' => now()->startOfYear(),
        ]);

        // Mixue Malaysia — Basic plan payment
        DB::table('payments')->insert([
            'organization_id' => 2,
            'subscription_id' => 2,
            'amount' => 2990.00,
            'currency' => 'MYR',
            'method' => 'card',
            'status' => 'completed',
            'reference_number' => 'PAY-2026-0002',
            'paid_at' => now()->startOfYear(),
            'created_at' => now()->startOfYear(),
            'updated_at' => now()->startOfYear(),
        ]);

        // =============================================
        // REGISTRATION REQUESTS
        // =============================================

        // Approved request that became Starbucks Malaysia org
        DB::table('registration_requests')->insert([
            'company_name' => 'Starbucks Malaysia Sdn Bhd',
            'contact_name' => 'Sarah Lee',
            'contact_email' => 'sarah@starbucks.com.my',
            'contact_phone' => '0171112222',
            'type' => 'beverage_company',
            'description' => 'We would like to deploy smart recycling bins at our outlets across Penang.',
            'status' => 'approved',
            'admin_notes' => 'Verified company registration. Approved for Pro plan.',
            'reviewed_by' => 1,
            'reviewed_at' => now()->subMonth(),
            'created_at' => now()->subMonths(2),
            'updated_at' => now()->subMonth(),
        ]);

        // Approved request that became Mixue Malaysia org
        DB::table('registration_requests')->insert([
            'company_name' => 'Mixue Malaysia Sdn Bhd',
            'contact_name' => 'Ahmad Rizal',
            'contact_email' => 'rizal@mixue.com.my',
            'contact_phone' => '0183334444',
            'type' => 'beverage_company',
            'description' => 'Interested in the smart recycling program for our Penang outlets.',
            'status' => 'approved',
            'admin_notes' => 'Verified. Starting with Basic plan.',
            'reviewed_by' => 1,
            'reviewed_at' => now()->subWeeks(3),
            'created_at' => now()->subMonths(1),
            'updated_at' => now()->subWeeks(3),
        ]);

        // =============================================
        // BRANDS
        // =============================================

        // Brand 1 — Starbucks (under Starbucks Malaysia org)
        DB::table('brands')->insert([
            'organization_id' => 1,
            'name' => 'Starbucks',
            'slug' => 'starbucks',
            'logo_path' => 'brands/starbucks.png',
            'description' => 'Premium coffee chain',
            'website' => 'https://www.starbucks.com.my',
            'point_multiplier' => 1.50,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Brand 2 — Mixue (under Mixue Malaysia org)
        DB::table('brands')->insert([
            'organization_id' => 2,
            'name' => 'Mixue',
            'slug' => 'mixue',
            'logo_path' => 'brands/mixue.png',
            'description' => 'Ice cream and tea chain',
            'website' => 'https://www.mixue.com',
            'point_multiplier' => 1.30,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // =============================================
        // INVITATIONS
        // =============================================

        // Sarah (brand owner) invited Jenny (store owner) — accepted
        DB::table('invitations')->insert([
            'organization_id' => 1,
            'invited_by' => 2,
            'email' => 'jenny@starbucks.com.my',
            'name' => 'Jenny Wong',
            'role' => 'store_owner',
            'status' => 'accepted',
            'admin_notes' => 'Approved. Gurney Plaza branch manager.',
            'approved_by' => 1,
            'approved_at' => now()->subWeeks(2),
            'accepted_at' => now()->subWeeks(2)->addDay(),
            'created_at' => now()->subWeeks(3),
            'updated_at' => now()->subWeeks(2)->addDay(),
        ]);

        // Sarah invited Wei Ming — accepted
        DB::table('invitations')->insert([
            'organization_id' => 1,
            'invited_by' => 2,
            'email' => 'weiming@starbucks.com.my',
            'name' => 'Lim Wei Ming',
            'role' => 'store_owner',
            'status' => 'accepted',
            'admin_notes' => 'Approved. Queensbay Mall branch manager.',
            'approved_by' => 1,
            'approved_at' => now()->subWeeks(2),
            'accepted_at' => now()->subWeeks(2)->addDay(),
            'created_at' => now()->subWeeks(3),
            'updated_at' => now()->subWeeks(2)->addDay(),
        ]);

        // Ahmad invited Nurul — accepted
        DB::table('invitations')->insert([
            'organization_id' => 2,
            'invited_by' => 5,
            'email' => 'nurul@mixue.com.my',
            'name' => 'Nurul Aisyah',
            'role' => 'store_owner',
            'status' => 'accepted',
            'admin_notes' => 'Approved. Komtar branch manager.',
            'approved_by' => 1,
            'approved_at' => now()->subWeeks(1),
            'accepted_at' => now()->subWeeks(1)->addDay(),
            'created_at' => now()->subWeeks(2),
            'updated_at' => now()->subWeeks(1)->addDay(),
        ]);

        // =============================================
        // OUTLETS
        // =============================================

        // Outlet 1 — Starbucks Gurney (Jenny manages)
        DB::table('outlets')->insert([
            'user_id' => 3,
            'brand_id' => 1,
            'name' => 'Gurney Plaza',
            'address' => 'Gurney Plaza, Persiaran Gurney, 10250 George Town, Penang',
            'latitude' => 5.4370,
            'longitude' => 100.3100,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Outlet 2 — Starbucks Queensbay (Wei Ming manages)
        DB::table('outlets')->insert([
            'user_id' => 4,
            'brand_id' => 1,
            'name' => 'Queensbay Mall',
            'address' => 'Queensbay Mall, 100 Persiaran Bayan Indah, 11900 Bayan Lepas, Penang',
            'latitude' => 5.3328,
            'longitude' => 100.3067,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Outlet 3 — Mixue Komtar (Nurul manages)
        DB::table('outlets')->insert([
            'user_id' => 6,
            'brand_id' => 2,
            'name' => 'Komtar',
            'address' => 'Komtar, Jalan Penang, 10000 George Town, Penang',
            'latitude' => 5.4141,
            'longitude' => 100.3288,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // =============================================
        // BINS
        // =============================================

        // Bin 1 — Starbucks Gurney
        DB::table('bins')->insert([
            'outlet_id' => 1,
            'serial_number' => 'MBS-SB-001',
            'api_token' => bin2hex(random_bytes(32)),
            'status' => 'active',
            'fill_level' => 0,
            'weight_grams' => 0,
            'capacity_liters' => 20.00,
            'sensor_levels' => json_encode(['level_25' => false, 'level_50' => false, 'level_75' => false, 'level_100' => false]),
            'latitude' => 5.4370,
            'longitude' => 100.3100,
            'paired_at' => now()->subWeek(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Bin 2 — Starbucks Gurney
        DB::table('bins')->insert([
            'outlet_id' => 1,
            'serial_number' => 'MBS-SB-002',
            'api_token' => bin2hex(random_bytes(32)),
            'status' => 'active',
            'fill_level' => 0,
            'weight_grams' => 0,
            'capacity_liters' => 20.00,
            'sensor_levels' => json_encode(['level_25' => false, 'level_50' => false, 'level_75' => false, 'level_100' => false]),
            'latitude' => 5.4371,
            'longitude' => 100.3101,
            'paired_at' => now()->subWeek(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Bin 3 — Starbucks Queensbay
        DB::table('bins')->insert([
            'outlet_id' => 2,
            'serial_number' => 'MBS-SB-003',
            'api_token' => bin2hex(random_bytes(32)),
            'status' => 'active',
            'fill_level' => 0,
            'weight_grams' => 0,
            'capacity_liters' => 20.00,
            'sensor_levels' => json_encode(['level_25' => false, 'level_50' => false, 'level_75' => false, 'level_100' => false]),
            'latitude' => 5.3328,
            'longitude' => 100.3067,
            'paired_at' => now()->subWeek(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Bin 4 — Mixue Komtar
        DB::table('bins')->insert([
            'outlet_id' => 3,
            'serial_number' => 'MBS-MX-001',
            'api_token' => bin2hex(random_bytes(32)),
            'status' => 'active',
            'fill_level' => 0,
            'weight_grams' => 0,
            'capacity_liters' => 20.00,
            'sensor_levels' => json_encode(['level_25' => false, 'level_50' => false, 'level_75' => false, 'level_100' => false]),
            'latitude' => 5.4141,
            'longitude' => 100.3288,
            'paired_at' => now()->subWeek(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // =============================================
        // PICKUP REQUESTS
        // =============================================

        // Automatic: bin 2 (Starbucks Gurney) hit 75% fill
        DB::table('pickup_requests')->insert([
            'bin_id' => 2,
            'request_type' => 'automatic',
            'requested_by' => null,
            'reason' => 'Fill level reached 75% threshold',
            'status' => 'completed',
            'assigned_to' => 7,
            'assigned_at' => now()->subDays(2),
            'completed_at' => now()->subDays(2)->addMinutes(52),
            'created_at' => now()->subDays(2)->subHour(),
            'updated_at' => now()->subDays(2)->addMinutes(52),
        ]);

        // Emergency: store owner reports contamination at Mixue Komtar
        DB::table('pickup_requests')->insert([
            'bin_id' => 4,
            'request_type' => 'emergency',
            'requested_by' => 6,
            'reason' => 'Liquid contamination — cups not rinsed, bin smells',
            'status' => 'pending',
            'assigned_to' => null,
            'assigned_at' => null,
            'completed_at' => null,
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
        ]);

        // =============================================
        // COLLECTION ROUTES
        // =============================================

        // Completed route — Kumar collected bins, optimized by Google Directions
        DB::table('collection_routes')->insert([
            'collector_id' => 7,
            'status' => 'completed',
            'depot_latitude' => 5.4141,
            'depot_longitude' => 100.3288,
            'depot_name' => 'Komtar Depot',
            'total_distance_km' => 30.80,
            'total_duration_min' => 52,
            'route_polyline' => 'sample_encoded_polyline_from_google_directions_api',
            'google_response' => json_encode(['note' => 'Full Google Directions API response stored here']),
            'started_at' => now()->subDays(2),
            'completed_at' => now()->subDays(2)->addMinutes(52),
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2)->addMinutes(52),
        ]);

        // =============================================
        // ROUTE STOPS
        // =============================================

        // Stop 1: Starbucks Gurney bin 2 (Google optimized this first)
        DB::table('route_stops')->insert([
            'collection_route_id' => 1,
            'bin_id' => 2,
            'pickup_request_id' => 1,
            'stop_order' => 1,
            'address' => '168A, Persiaran Gurney, 10350 George Town, Pulau Pinang',
            'distance_km' => 4.00,
            'duration_min' => 9,
            'status' => 'completed',
            'eta' => now()->subDays(2)->addMinutes(9),
            'completed_at' => now()->subDays(2)->addMinutes(11),
            'completed_latitude' => 5.4371,
            'completed_longitude' => 100.3101,
            'proof_image_path' => 'proofs/route1-stop1.jpg',
            'skip_reason' => '',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2)->addMinutes(11),
        ]);

        // =============================================
        // BIN SESSIONS
        // =============================================

        // Session 1 — Mei Ling at Starbucks Gurney bin 1 (gold standard: separated + rinsed)
        DB::table('bin_sessions')->insert([
            'bin_id' => 1,
            'user_id' => 8,
            'status' => 'completed',
            'cup_rinsed' => true,
            'started_at' => now()->subHours(3),
            'ended_at' => now()->subHours(3)->addMinutes(5),
            'created_at' => now()->subHours(3),
            'updated_at' => now()->subHours(3)->addMinutes(5),
        ]);

        // Session 2 — Mei Ling at Mixue Komtar bin (no rinse, general intake)
        DB::table('bin_sessions')->insert([
            'bin_id' => 4,
            'user_id' => 8,
            'status' => 'completed',
            'cup_rinsed' => false,
            'started_at' => now()->subHours(1),
            'ended_at' => now()->subHours(1)->addMinutes(4),
            'created_at' => now()->subHours(1),
            'updated_at' => now()->subHours(1)->addMinutes(4),
        ]);

        // =============================================
        // DETECTION EVENTS
        // =============================================

        // Session 1: Starbucks cup — properly separated via cup slot
        DB::table('detection_events')->insert([
            'bin_session_id' => 1,
            'waste_type' => 'paper_cup',
            'input_method' => 'cup_slot',
            'detected_brand_id' => 1,
            'confidence' => 92,
            'image_path' => 'detections/session1-cup.jpg',
            'ai_output' => json_encode([
                'model' => 'yolov8-mobius',
                'classes' => ['paper_cup' => 0.92],
                'brand' => ['starbucks' => 0.88],
                'bounding_box' => [120, 80, 340, 420],
            ]),
            'created_at' => now()->subHours(3)->addMinute(),
            'updated_at' => now()->subHours(3)->addMinute(),
        ]);

        // Session 1: lid — separated via lid slot
        DB::table('detection_events')->insert([
            'bin_session_id' => 1,
            'waste_type' => 'lid',
            'input_method' => 'lid_slot',
            'detected_brand_id' => 1,
            'confidence' => 87,
            'image_path' => 'detections/session1-lid.jpg',
            'ai_output' => json_encode([
                'model' => 'yolov8-mobius',
                'classes' => ['lid' => 0.87],
                'brand' => [],
                'bounding_box' => [150, 100, 300, 280],
                'brand_inherited_from' => 'session_cup',
            ]),
            'created_at' => now()->subHours(3)->addMinutes(2),
            'updated_at' => now()->subHours(3)->addMinutes(2),
        ]);

        // Session 1: straw — separated via straw slot
        DB::table('detection_events')->insert([
            'bin_session_id' => 1,
            'waste_type' => 'straw',
            'input_method' => 'straw_slot',
            'detected_brand_id' => 1,
            'confidence' => 95,
            'image_path' => 'detections/session1-straw.jpg',
            'ai_output' => json_encode([
                'model' => 'yolov8-mobius',
                'classes' => ['straw' => 0.95],
                'brand' => [],
                'bounding_box' => [200, 50, 240, 450],
                'brand_inherited_from' => 'session_cup',
            ]),
            'created_at' => now()->subHours(3)->addMinutes(3),
            'updated_at' => now()->subHours(3)->addMinutes(3),
        ]);

        // Session 2: Mixue cup — threw everything in general intake
        DB::table('detection_events')->insert([
            'bin_session_id' => 2,
            'waste_type' => 'plastic_cup',
            'input_method' => 'general_intake',
            'detected_brand_id' => 2,
            'confidence' => 89,
            'image_path' => 'detections/session2-cup.jpg',
            'ai_output' => json_encode([
                'model' => 'yolov8-mobius',
                'classes' => ['plastic_cup' => 0.89],
                'brand' => ['mixue' => 0.85],
                'bounding_box' => [100, 60, 360, 440],
            ]),
            'created_at' => now()->subHours(1)->addMinute(),
            'updated_at' => now()->subHours(1)->addMinute(),
        ]);

        // =============================================
        // RECYCLING TRANSACTIONS
        // =============================================

        // Session 1: cup(15)+lid(5)+straw(3)=23 × 2.0 behavior(separated+rinsed) × 1.5 brand = 69
        DB::table('recycling_transactions')->insert([
            'user_id' => 8,
            'bin_session_id' => 1,
            'type' => 'earned',
            'points' => 69,
            'description' => 'Gold standard: 3 items separated + rinsed at Starbucks Gurney — behavior 2.0x, brand 1.5x',
            'created_at' => now()->subHours(3)->addMinutes(5),
            'updated_at' => now()->subHours(3)->addMinutes(5),
        ]);

        // Session 2: cup only(12) × 1.0 behavior(general intake, no rinse) × 1.3 brand = 15
        DB::table('recycling_transactions')->insert([
            'user_id' => 8,
            'bin_session_id' => 2,
            'type' => 'earned',
            'points' => 15,
            'description' => 'General intake: 1 item unseparated at Mixue Komtar — behavior 1.0x, brand 1.3x',
            'created_at' => now()->subHours(1)->addMinutes(4),
            'updated_at' => now()->subHours(1)->addMinutes(4),
        ]);

        // =============================================
        // VOUCHER TEMPLATES
        // =============================================

        // Starbucks voucher: RM5 off
        DB::table('voucher_templates')->insert([
            'brand_id' => 1,
            'name' => 'RM5 Off Any Drink',
            'description' => 'Get RM5 off any handcrafted beverage at Starbucks',
            'type' => 'discount',
            'value' => 5.00,
            'points_required' => 100,
            'valid_from' => now()->startOfMonth(),
            'valid_until' => now()->endOfMonth()->addMonths(2),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Mixue voucher: free ice cream
        DB::table('voucher_templates')->insert([
            'brand_id' => 2,
            'name' => 'Free Ice Cream Cone',
            'description' => 'Redeem a free vanilla ice cream cone at any Mixue outlet',
            'type' => 'free_item',
            'value' => 3.00,
            'points_required' => 50,
            'valid_from' => now()->startOfMonth(),
            'valid_until' => now()->endOfMonth()->addMonth(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // =============================================
        // VOUCHER ALLOCATIONS
        // =============================================

        // Starbucks Gurney gets 50 RM5 vouchers
        DB::table('voucher_allocations')->insert([
            'voucher_template_id' => 1,
            'outlet_id' => 1,
            'quota' => 50,
            'claimed_count' => 0,
            'valid_from' => now()->startOfMonth(),
            'valid_until' => now()->endOfMonth()->addMonths(2),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Starbucks Queensbay gets 30 RM5 vouchers
        DB::table('voucher_allocations')->insert([
            'voucher_template_id' => 1,
            'outlet_id' => 2,
            'quota' => 30,
            'claimed_count' => 0,
            'valid_from' => now()->startOfMonth(),
            'valid_until' => now()->endOfMonth()->addMonths(2),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Mixue Komtar gets 40 ice cream vouchers
        DB::table('voucher_allocations')->insert([
            'voucher_template_id' => 2,
            'outlet_id' => 3,
            'quota' => 40,
            'claimed_count' => 0,
            'valid_from' => now()->startOfMonth(),
            'valid_until' => now()->endOfMonth()->addMonth(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // =============================================
        // VOUCHER CLAIMS
        // =============================================
        // (None yet — Mei Ling has 49 points, hasn't hit 50 for Mixue or 100 for Starbucks)
    }
}
