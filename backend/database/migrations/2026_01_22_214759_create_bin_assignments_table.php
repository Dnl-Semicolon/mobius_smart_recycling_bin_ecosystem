<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bin_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bin_id')->constrained()->cascadeOnDelete();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->timestamp('assigned_at');
            $table->timestamp('unassigned_at')->nullable();

            $table->index('bin_id');
            $table->index('outlet_id');
            $table->index(['bin_id', 'unassigned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bin_assignments');
    }
};
