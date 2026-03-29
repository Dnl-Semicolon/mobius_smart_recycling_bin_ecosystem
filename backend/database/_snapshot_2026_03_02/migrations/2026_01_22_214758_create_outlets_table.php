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
            $table->string('name');
            $table->string('address', 500);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->string('contact_email')->nullable();
            $table->string('operating_hours', 500)->nullable();
            $table->string('contract_status')->default(ContractStatus::Pending->value);
            $table->text('notes')->nullable();
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
