<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->date('lease_start');
            $table->date('lease_end')->nullable();
            $table->decimal('agreed_rent', 12, 2);
            $table->enum('payment_frequency', ['monthly', 'fortnightly', 'weekly'])->default('monthly');
            $table->enum('status', ['active', 'expired', 'terminated'])->default('active');
            $table->enum('tenant_status', ['in_good_standing', 'overdue', 'in_arrears'])->default('in_good_standing');
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenancies');
    }
};
