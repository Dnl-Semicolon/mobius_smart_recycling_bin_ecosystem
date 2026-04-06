<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bin_collection_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bin_id')->constrained()->cascadeOnDelete();
            $table->foreignId('collector_id')->constrained('users');
            $table->foreignId('route_stop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pickup_request_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('fill_level_before'); // what it was before reset
            $table->decimal('collected_latitude', 10, 7)->nullable();
            $table->decimal('collected_longitude', 10, 7)->nullable();
            $table->timestamps();

            $table->index(['bin_id', 'created_at']);
            $table->index(['collector_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bin_collection_logs');
    }
};
