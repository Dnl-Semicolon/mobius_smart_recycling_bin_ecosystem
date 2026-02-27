<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'pickup_requests_status_created_at_idx');
            $table->index(['claimed_by', 'status', 'claimed_at'], 'pickup_requests_claimed_status_claimed_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            $table->dropIndex('pickup_requests_status_created_at_idx');
            $table->dropIndex('pickup_requests_claimed_status_claimed_at_idx');
        });
    }
};
