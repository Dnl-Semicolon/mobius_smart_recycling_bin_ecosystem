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
        Schema::table('collection_routes', function (Blueprint $table) {
            $table->text('route_geometry')->nullable()->after('vroom_response');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collection_routes', function (Blueprint $table) {
            $table->dropColumn('route_geometry');
        });
    }
};
