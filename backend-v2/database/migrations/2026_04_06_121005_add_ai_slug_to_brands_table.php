<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            // Short slug used for AI brand detection lookup (e.g. 'starbucks', 'zus')
            // Separate from the URL slug which is generated from company legal name
            $table->string('ai_slug')->nullable()->unique()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn('ai_slug');
        });
    }
};
