<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * These tables are created by the monolithic 0001_01_01_000000 migration.
 * This file exists only for reference — it is a no-op.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('bin_sessions')) {
            Schema::create('bin_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('bin_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->enum('status', ['active', 'completed', 'expired', 'terminated'])->default('active');
                $table->boolean('cup_rinsed')->default(false);
                $table->timestamp('started_at')->nullable();
                $table->timestamp('ended_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('detection_events')) {
            Schema::create('detection_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('bin_session_id')->constrained()->cascadeOnDelete();
                $table->enum('waste_type', ['paper_cup', 'plastic_cup', 'lid', 'straw', 'napkin', 'liquid_waste']);
                $table->enum('input_method', ['cup_slot', 'lid_slot', 'straw_slot', 'general_intake'])->default('general_intake');
                $table->foreignId('detected_brand_id')->nullable()->constrained('brands')->nullOnDelete();
                $table->unsignedTinyInteger('confidence')->default(0);
                $table->string('image_path')->nullable();
                $table->json('ai_output')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('recycling_transactions')) {
            Schema::create('recycling_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('bin_session_id')->nullable()->constrained()->nullOnDelete();
                $table->enum('type', ['earned', 'spent', 'bonus', 'expired'])->default('earned');
                $table->integer('points');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Intentionally left blank — managed by the monolithic migration
    }
};
