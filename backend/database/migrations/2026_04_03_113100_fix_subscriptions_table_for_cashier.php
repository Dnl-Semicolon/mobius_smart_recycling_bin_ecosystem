<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The rebuild migration created a custom "subscriptions" table with organization_id,
        // but Cashier needs a "subscriptions" table with user_id and Stripe columns.
        // Rename the custom one and create Cashier's expected tables.

        if (Schema::hasTable('subscriptions') && Schema::hasColumn('subscriptions', 'organization_id')) {
            Schema::rename('subscriptions', 'organization_subscriptions');
        }

        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'subscription_id')) {
            // Update payments FK reference if needed — for now just leave it
            // (the payments table still references the old subscriptions table by ID, which is now renamed)
        }

        if (! Schema::hasTable('subscriptions')) {
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
        }

        if (! Schema::hasTable('subscription_items')) {
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
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_items');
        Schema::dropIfExists('subscriptions');

        if (Schema::hasTable('organization_subscriptions')) {
            Schema::rename('organization_subscriptions', 'subscriptions');
        }
    }
};
