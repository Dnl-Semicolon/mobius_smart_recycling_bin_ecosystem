<?php

use App\Enums\BinStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bins', function (Blueprint $table) {
            $table->id();
            $table->string('serial_number', 50)->unique();
            $table->unsignedTinyInteger('fill_level')->default(0);
            $table->string('status')->default(BinStatus::Active->value);
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('fill_level');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bins');
    }
};
