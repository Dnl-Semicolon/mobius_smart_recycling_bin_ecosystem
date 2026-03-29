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
        Schema::create('collection_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained()->cascadeOnDelete();
            $table->foreignId('collector_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->json('stops');
            $table->decimal('total_distance_km', 8, 2)->nullable();
            $table->unsignedInteger('total_duration_min')->nullable();
            $table->timestamp('estimated_start_time')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('vroom_request')->nullable();
            $table->json('vroom_response')->nullable();
            $table->text('route_geometry')->nullable();
            $table->timestamps();

            $table->index(['collector_id', 'status']);
            $table->index(['zone_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_routes');
    }
};
