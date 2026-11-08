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
        Schema::table('company_settings', function (Blueprint $table) {
            $table->string('support_phone', 50)->nullable()->default('920000488')->after('stamp_path');
            $table->string('support_email', 190)->nullable()->default('support@rounder.sa')->after('support_phone');
            $table->string('support_working_hours', 190)->nullable()->default('الأحد إلى الخميس، 9 ص - 6 م')->after('support_email');
            $table->boolean('support_chat_enabled')->default(true)->after('support_working_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn([
                'support_phone',
                'support_email',
                'support_working_hours',
                'support_chat_enabled',
            ]);
        });
    }
};

