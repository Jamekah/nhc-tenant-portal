<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();
            $table->string('property_code', 50)->unique();
            $table->enum('type', ['residential', 'institutional', 'land']);
            $table->string('title');
            $table->text('address');
            $table->string('suburb', 100)->nullable();
            $table->string('city', 100);
            $table->string('province', 100);
            $table->tinyInteger('bedrooms')->unsigned()->nullable();
            $table->decimal('size_sqm', 10, 2)->nullable();
            $table->decimal('monthly_rent', 12, 2);
            $table->enum('payment_frequency', ['monthly', 'fortnightly', 'weekly'])->default('monthly');
            $table->enum('status', ['available', 'occupied', 'under_maintenance', 'decommissioned'])->default('available');
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
