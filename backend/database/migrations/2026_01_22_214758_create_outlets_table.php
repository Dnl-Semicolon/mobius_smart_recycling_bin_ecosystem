<?php

use App\Enums\ContractStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outlets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->string('name');
            $table->string('address', 500);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->string('contact_email')->nullable();
            $table->text('operating_hours')->nullable();
            $table->string('contract_status')->default(ContractStatus::Pending->value);
            $table->text('notes')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('contract_status');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outlets');
    }
};
