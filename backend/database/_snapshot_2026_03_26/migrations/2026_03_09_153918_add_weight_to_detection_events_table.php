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
        Schema::table('detection_events', function (Blueprint $table) {
            $table->unsignedInteger('weight_g')->nullable()->after('confidence');
        });
    }

    public function down(): void
    {
        Schema::table('detection_events', function (Blueprint $table) {
            $table->dropColumn('weight_g');
        });
    }
};
