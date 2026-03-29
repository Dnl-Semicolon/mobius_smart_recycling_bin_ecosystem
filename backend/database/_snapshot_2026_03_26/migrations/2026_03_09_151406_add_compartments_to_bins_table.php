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
        Schema::table('bins', function (Blueprint $table) {
            $table->json('compartments')->nullable()->after('fill_level');
        });
    }

    public function down(): void
    {
        Schema::table('bins', function (Blueprint $table) {
            $table->dropColumn('compartments');
        });
    }
};
