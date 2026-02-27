<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detection_events', function (Blueprint $table) {
            $table->string('waste_type')->nullable()->change();
            $table->unsignedTinyInteger('confidence')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('detection_events', function (Blueprint $table) {
            $table->string('waste_type')->nullable(false)->change();
            $table->unsignedTinyInteger('confidence')->nullable(false)->change();
        });
    }
};
