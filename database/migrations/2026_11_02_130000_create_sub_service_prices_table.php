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
        Schema::create('sub_service_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_profile_id')->constrained('provider_profiles')->cascadeOnDelete();
            $table->foreignId('sub_service_id')->constrained('sub_services')->cascadeOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->decimal('price', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['provider_profile_id', 'sub_service_id']);
            $table->index(['region_id']);
        });

        Schema::create('sub_service_price_cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_service_price_id')->constrained('sub_service_prices')->cascadeOnDelete();
            $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();

            $table->unique(['sub_service_price_id', 'city_id'], 'price_city_unique');
            $table->index('city_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_service_price_cities');
        Schema::dropIfExists('sub_service_prices');
    }
};
