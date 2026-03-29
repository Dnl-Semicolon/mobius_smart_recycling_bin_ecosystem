<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('notes');
            $table->text('operating_hours')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->dropColumn('photo_path');
            $table->string('operating_hours', 500)->nullable()->change();
        });
    }
};
