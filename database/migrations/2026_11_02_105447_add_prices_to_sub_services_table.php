<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // migration: add_prices_to_sub_services_table
public function up(): void
{
    Schema::table('sub_services', function (Blueprint $table) {
        $table->decimal('price_eastern', 10, 2)->nullable()->after('code');
        $table->decimal('price_qassim_madinah_kharj', 10, 2)->nullable()->after('price_eastern');
    });
}

public function down(): void
{
    Schema::table('sub_services', function (Blueprint $table) {
        $table->dropColumn(['price_eastern', 'price_qassim_madinah_kharj']);
    });
}

};
