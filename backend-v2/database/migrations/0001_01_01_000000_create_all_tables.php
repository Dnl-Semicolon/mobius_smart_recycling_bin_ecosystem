<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // =============================================
        // ORGANIZATIONS
        // =============================================
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['beverage_company', 'recycling_company', 'government']);
            $table->text('description')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('website')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // =============================================
        // SERVICE PLANS
        // =============================================
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price_monthly', 10, 2);
            $table->decimal('price_yearly', 10, 2);

            // Enforced limits (null = unlimited)
            $table->unsignedInteger('bin_limit')->nullable();
            $table->unsignedInteger('outlet_limit')->nullable();
            $table->unsignedInteger('staff_limit')->nullable();
            $table->boolean('api_access')->default(false);

            // Display-only metadata (not enforced)
            $table->json('features')->nullable()->comment('cosmetic: analytics level, support tier, etc.');

            // Stripe
            $table->string('stripe_price_id')->nullable()->comment('monthly Stripe Price ID');
            $table->string('stripe_price_yearly_id')->nullable()->comment('yearly Stripe Price ID');

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // =============================================
        // USERS
        // =============================================
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Fortify 2FA
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();

            $table->string('phone')->nullable()->unique();
            $table->timestamp('phone_verified_at')->nullable()->comment('OTP verification timestamp');
            $table->string('profile_photo_path')->nullable();
            $table->json('roles');

            // Recycling engagement
            $table->unsignedInteger('points_balance')->default(0);
            $table->unsignedInteger('current_streak')->default(0);
            $table->unsignedInteger('longest_streak')->default(0);
            $table->timestamp('last_recycled_at')->nullable();

            // Stripe (Cashier)
            $table->string('stripe_id')->nullable()->index();
            $table->string('pm_type')->nullable();
            $table->string('pm_last_four', 4)->nullable();
            $table->timestamp('trial_ends_at')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });

        // Laravel auth infrastructure
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // =============================================
        // ORGANIZATION SUBSCRIPTIONS
        // =============================================
        Schema::create('organization_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained();
            $table->foreignId('plan_id')->constrained();
            $table->enum('status', ['pending_payment', 'active', 'past_due', 'cancelled', 'expired'])->default('pending_payment');
            $table->enum('billing_interval', ['monthly', 'yearly'])->default('monthly');

            // Per-org overrides (null = use plan defaults)
            $table->unsignedInteger('custom_bin_limit')->nullable();
            $table->unsignedInteger('custom_outlet_limit')->nullable();
            $table->unsignedInteger('custom_staff_limit')->nullable();
            $table->decimal('custom_price_monthly', 10, 2)->nullable()->comment('display only — Stripe has actual charge');
            $table->text('notes')->nullable()->comment('admin internal notes about the deal');
            $table->string('stripe_price_id')->nullable()->comment('org-specific Stripe Price for custom deals');

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('renews_at')->nullable();
            $table->timestamps();
        });

        // =============================================
        // PAYMENTS
        // =============================================
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained();
            $table->foreignId('subscription_id')->constrained('organization_subscriptions');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('MYR');
            $table->enum('method', ['card', 'bank_transfer', 'manual']);
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('reference_number')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        // =============================================
        // REGISTRATION REQUESTS
        // =============================================
        Schema::create('registration_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('selected_plan_id')->nullable()->constrained('plans');
            $table->string('company_name');
            $table->string('contact_name');
            $table->string('contact_email');
            $table->string('contact_phone');
            $table->enum('type', ['beverage_company', 'recycling_company', 'government']);
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            // Verification (merged from add_verification migration)
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('email_verification_token')->nullable();
            $table->timestamps();
        });

        // =============================================
        // BRANDS
        // =============================================
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo_path')->nullable();
            $table->text('description')->nullable();
            $table->string('website')->nullable();
            $table->decimal('point_multiplier', 3, 2)->default(1.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // =============================================
        // INVITATIONS
        // =============================================
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained();
            $table->foreignId('invited_by')->constrained('users');
            $table->string('email');
            $table->string('name');
            $table->enum('role', ['store_owner', 'collector']);
            $table->enum('status', ['pending', 'approved', 'rejected', 'accepted'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
        });

        // =============================================
        // OUTLETS
        // =============================================
        Schema::create('outlets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->comment('store owner');
            $table->foreignId('brand_id')->constrained();
            $table->string('name');
            $table->string('address')->nullable()->comment('full formatted address from Google');
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postcode', 10)->nullable();
            $table->string('country')->nullable()->default('Malaysia');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // =============================================
        // BINS
        // =============================================
        Schema::create('bins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->nullable()->constrained()->comment('assigned after pairing');
            $table->string('serial_number')->unique();
            $table->string('api_token', 64)->unique()->nullable()->comment('generated at pairing, bin client auth');
            $table->enum('status', ['unpaired', 'active', 'maintenance', 'offline'])->default('unpaired');
            $table->unsignedTinyInteger('fill_level')->default(0)->comment('0-100 cached percentage');
            $table->unsignedInteger('weight_grams')->default(0)->comment('current weight from load cell');
            $table->decimal('capacity_liters', 5, 2)->default(20.00)->comment('total bin volume capacity');
            $table->json('sensor_levels')->comment('IR sensor states: {"level_25": false, ...}');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamp('paired_at')->nullable();
            $table->timestamp('last_pickup_at')->nullable()->comment('when bin was last collected');
            $table->timestamps();
        });

        // =============================================
        // PICKUP REQUESTS
        // =============================================
        Schema::create('pickup_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bin_id')->constrained();
            $table->enum('request_type', ['automatic', 'emergency'])->default('automatic');
            $table->foreignId('requested_by')->nullable()->constrained('users')->comment('store owner for emergency');
            $table->text('reason')->nullable()->comment('why pickup is needed');
            $table->enum('status', ['pending', 'assigned', 'completed', 'cancelled'])->default('pending');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->comment('collector');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        // =============================================
        // COLLECTION ROUTES
        // =============================================
        Schema::create('collection_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collector_id')->constrained('users');
            $table->enum('status', ['pending', 'accepted', 'in_progress', 'completed', 'rejected'])->default('pending');
            $table->decimal('depot_latitude', 10, 7)->comment('start/end point');
            $table->decimal('depot_longitude', 10, 7);
            $table->string('depot_name');
            $table->decimal('total_distance_km', 8, 2);
            $table->unsignedInteger('total_duration_min');
            $table->text('route_polyline')->nullable()->comment('encoded polyline from Google Directions API');
            $table->json('google_response')->nullable()->comment('full API response for debugging/replay');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['collector_id', 'status']);
        });

        // =============================================
        // ROUTE STOPS
        // =============================================
        Schema::create('route_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_route_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bin_id')->constrained();
            $table->foreignId('pickup_request_id')->constrained();
            $table->unsignedTinyInteger('stop_order')->comment('optimized order from Google');
            $table->string('address')->comment('resolved address from Google');
            $table->decimal('distance_km', 8, 2)->comment('distance of this leg');
            $table->unsignedInteger('duration_min')->comment('duration of this leg');
            $table->enum('status', ['pending', 'completed', 'skipped'])->default('pending');
            $table->timestamp('eta')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('completed_latitude', 10, 7)->nullable()->comment('GPS where collector marked complete');
            $table->decimal('completed_longitude', 10, 7)->nullable();
            $table->string('proof_image_path')->nullable()->comment('photo proof of collection');
            $table->string('skip_reason')->nullable();
            $table->timestamps();
        });

        // =============================================
        // BIN SESSIONS
        // =============================================
        Schema::create('bin_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bin_id')->constrained();
            $table->foreignId('user_id')->nullable()->constrained()->comment('linked when user scans QR');
            $table->enum('status', ['active', 'completed', 'expired', 'terminated'])->default('active');
            $table->boolean('cup_rinsed')->default(false)->comment('wash basin was used during session');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });

        // =============================================
        // DETECTION EVENTS
        // =============================================
        Schema::create('detection_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bin_session_id')->constrained();
            $table->enum('waste_type', ['paper_cup', 'plastic_cup', 'lid', 'straw', 'napkin', 'liquid_waste']);
            $table->enum('input_method', ['cup_slot', 'lid_slot', 'straw_slot', 'general_intake'])->comment('which slot');
            $table->foreignId('detected_brand_id')->nullable()->constrained('brands')->comment('cup brand from AI');
            $table->unsignedTinyInteger('confidence')->comment('0-100');
            $table->string('image_path')->nullable();
            $table->json('ai_output')->nullable()->comment('raw model response: bounding boxes, probabilities');
            $table->timestamps();
        });

        // =============================================
        // RECYCLING TRANSACTIONS
        // =============================================
        Schema::create('recycling_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('bin_session_id')->nullable()->constrained()->comment('null for spent/bonus/expired');
            $table->enum('type', ['earned', 'spent', 'bonus', 'expired']);
            $table->integer('points')->comment('positive for earned/bonus, negative for spent/expired');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // =============================================
        // VOUCHER TEMPLATES
        // =============================================
        Schema::create('voucher_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['discount', 'free_item', 'cashback']);
            $table->decimal('value', 10, 2)->comment('e.g. 5.00 for RM5 off');
            $table->unsignedInteger('points_required');
            $table->timestamp('valid_from');
            $table->timestamp('valid_until');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // =============================================
        // VOUCHER ALLOCATIONS
        // =============================================
        Schema::create('voucher_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_template_id')->constrained();
            $table->foreignId('outlet_id')->constrained();
            $table->unsignedInteger('quota');
            $table->unsignedInteger('claimed_count')->default(0);
            $table->timestamp('valid_from');
            $table->timestamp('valid_until');
            $table->timestamps();
        });

        // =============================================
        // VOUCHER CLAIMS
        // =============================================
        Schema::create('voucher_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_template_id')->constrained();
            $table->foreignId('voucher_allocation_id')->nullable()->constrained()->comment('null for normal vouchers');
            $table->foreignId('user_id')->constrained();
            $table->unsignedInteger('points_spent');
            $table->enum('status', ['claimed', 'redeemed', 'expired'])->default('claimed');
            $table->string('qr_code')->unique()->nullable()->comment('unique QR for redemption scanning');
            $table->timestamp('claimed_at');
            $table->timestamp('expires_at');
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamps();
        });

        // =============================================
        // COLLECTOR AGENCIES (merged from dev migration)
        // =============================================
        Schema::create('collector_agencies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('contact_person');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('logo_path')->nullable();
            $table->text('description')->nullable();
            $table->integer('fleet_size')->default(0);
            $table->text('coverage_area')->nullable();
            $table->string('status')->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('agency_collector', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collector_agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
            $table->unique(['collector_agency_id', 'user_id']);
        });

        // =============================================
        // BRAND APPLICATIONS (merged from dev migration)
        // =============================================
        Schema::create('brand_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->string('brand_name');
            $table->text('description')->nullable();
            $table->string('website_url')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('contact_person');
            $table->string('contact_email');
            $table->string('contact_phone')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        // =============================================
        // APP NOTIFICATIONS (merged from dev migration)
        // =============================================
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('title');
            $table->text('body');
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // =============================================
        // STRIPE SUBSCRIPTIONS (Cashier)
        // =============================================
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('type');
            $table->string('stripe_id')->unique();
            $table->string('stripe_status');
            $table->string('stripe_price')->nullable();
            $table->integer('quantity')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'stripe_status']);
        });

        Schema::create('subscription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id');
            $table->string('stripe_id')->unique();
            $table->string('stripe_product');
            $table->string('stripe_price');
            $table->integer('quantity')->nullable();
            $table->timestamps();
            $table->index(['subscription_id', 'stripe_price']);
        });

        // =============================================
        // CACHE (Laravel infrastructure)
        // =============================================
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        // =============================================
        // JOBS (Laravel infrastructure)
        // =============================================
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('subscription_items');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('app_notifications');
        Schema::dropIfExists('brand_applications');
        Schema::dropIfExists('agency_collector');
        Schema::dropIfExists('collector_agencies');
        Schema::dropIfExists('voucher_claims');
        Schema::dropIfExists('voucher_allocations');
        Schema::dropIfExists('voucher_templates');
        Schema::dropIfExists('recycling_transactions');
        Schema::dropIfExists('detection_events');
        Schema::dropIfExists('bin_sessions');
        Schema::dropIfExists('route_stops');
        Schema::dropIfExists('collection_routes');
        Schema::dropIfExists('pickup_requests');
        Schema::dropIfExists('bins');
        Schema::dropIfExists('outlets');
        Schema::dropIfExists('invitations');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('registration_requests');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('organization_subscriptions');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('plans');
        Schema::dropIfExists('organizations');
    }
};
