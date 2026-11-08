<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * إضافة عمود sub_service_id لجدول bookings مع ربطه بجدول sub_services.
 */
return new class extends Migration {
    /**
     * تشغيل الترحيل.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('sub_service_id')->nullable()->after('service_id')->constrained('sub_services')->nullOnDelete();
        });
    }

    /**
     * التراجع عن الترحيل.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sub_service_id');
        });
    }
};
