<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('collector_agencies')) {
            Schema::create('collector_agencies', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('contact_person');
                $table->string('email')->unique();
                $table->string('phone')->nullable();
                $table->string('logo_path')->nullable();
                $table->text('description')->nullable();
                $table->integer('fleet_size')->default(0);
                $table->text('coverage_area')->nullable();
                $table->string('status')->default('pending');
                $table->text('rejection_reason')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('agency_collector')) {
            Schema::create('agency_collector', function (Blueprint $table) {
                $table->id();
                $table->foreignId('collector_agency_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('status')->default('active');
                $table->timestamp('invited_at')->nullable();
                $table->timestamp('joined_at')->nullable();
                $table->timestamps();

                $table->unique(['collector_agency_id', 'user_id']);
            });
        }

        if (! Schema::hasTable('app_notifications')) {
            Schema::create('app_notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('type');
                $table->string('title');
                $table->text('body');
                $table->json('data')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('brand_applications')) {
            Schema::create('brand_applications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('status')->default('pending');
                $table->string('brand_name');
                $table->text('description')->nullable();
                $table->string('website_url')->nullable();
                $table->string('logo_path')->nullable();
                $table->string('contact_person');
                $table->string('contact_email');
                $table->string('contact_phone')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_applications');
        Schema::dropIfExists('app_notifications');
        Schema::dropIfExists('agency_collector');
        Schema::dropIfExists('collector_agencies');
    }
};
