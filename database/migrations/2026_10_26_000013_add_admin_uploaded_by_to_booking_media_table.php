<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * دعم تسجيل صاحب الرفع الإداري بشكل منفصل في وسائط الحجوزات،
 * وجعل uploaded_by يقبل null عند الرفع من لوحة الإدارة.
 */
return new class extends Migration {
    /**
     * تشغيل الترحيل.
     */
    public function up(): void
    {
        Schema::table('booking_media', function (Blueprint $table) {
            $table->unsignedBigInteger('uploaded_by')->nullable()->change();
            $table->foreignId('admin_uploaded_by')->nullable()->after('uploaded_by')->constrained('admins')->restrictOnDelete();
        });
    }

    /**
     * التراجع عن الترحيل.
     */
    public function down(): void
    {
        Schema::table('booking_media', function (Blueprint $table) {
            $table->dropConstrainedForeignId('admin_uploaded_by');
            $table->unsignedBigInteger('uploaded_by')->nullable(false)->change();
        });
    }
};
