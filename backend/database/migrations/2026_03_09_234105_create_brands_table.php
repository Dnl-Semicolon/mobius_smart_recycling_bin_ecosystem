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
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo_path')->nullable();
            $table->string('primary_color', 7)->nullable();
            $table->text('description')->nullable();
            $table->decimal('points_multiplier', 3, 2)->default(1.00);
            $table->integer('rewards_budget')->default(0);
            $table->boolean('active')->default(true);
            $table->string('status')->default('active');
            $table->string('contact_person')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('website_url')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Deferred FK constraints for tables created before brands
        Schema::table('outlets', function (Blueprint $table) {
            $table->foreign('brand_id')->references('id')->on('brands')->nullOnDelete();
        });
        Schema::table('detection_events', function (Blueprint $table) {
            $table->foreign('detected_brand_id')->references('id')->on('brands')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detection_events', function (Blueprint $table) {
            $table->dropForeign(['detected_brand_id']);
        });
        Schema::table('outlets', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
        });
        Schema::dropIfExists('brands');
    }
};
