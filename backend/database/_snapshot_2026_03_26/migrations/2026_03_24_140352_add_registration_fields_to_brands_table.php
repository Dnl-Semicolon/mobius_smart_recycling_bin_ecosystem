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
        Schema::table('brands', function (Blueprint $table) {
            $table->string('status')->default('active')->after('active');
            $table->string('contact_person')->nullable()->after('status');
            $table->string('contact_email')->nullable()->after('contact_person');
            $table->string('contact_phone')->nullable()->after('contact_email');
            $table->string('website_url')->nullable()->after('contact_phone');
            $table->text('rejection_reason')->nullable()->after('website_url');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete()->after('rejection_reason');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->after('reviewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'status', 'contact_person', 'contact_email', 'contact_phone',
                'website_url', 'rejection_reason', 'reviewed_by', 'reviewed_at', 'user_id',
            ]);
        });
    }
};
