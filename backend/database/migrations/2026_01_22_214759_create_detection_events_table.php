<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detection_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bin_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('waste_type')->nullable();
            $table->unsignedTinyInteger('confidence')->nullable();
            $table->unsignedInteger('weight_g')->nullable();
            $table->unsignedBigInteger('detected_brand_id')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('feedback_accurate')->nullable();
            $table->timestamp('detected_at');
            $table->timestamps();

            $table->index('bin_id');
            $table->index('waste_type');
            $table->index('detected_at');
            $table->index('confidence');
            $table->index('detected_brand_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detection_events');
    }
};
