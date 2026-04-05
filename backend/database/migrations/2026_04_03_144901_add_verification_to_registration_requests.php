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
        Schema::table('registration_requests', function (Blueprint $table) {
            $table->timestamp('phone_verified_at')->nullable()->after('reviewed_at');
            $table->timestamp('email_verified_at')->nullable()->after('phone_verified_at');
            $table->string('email_verification_token')->nullable()->after('email_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('registration_requests', function (Blueprint $table) {
            $table->dropColumn(['phone_verified_at', 'email_verified_at', 'email_verification_token']);
        });
    }
};
