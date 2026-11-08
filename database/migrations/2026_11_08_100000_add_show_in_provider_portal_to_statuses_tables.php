<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('booking_statuses', 'show_in_provider_portal')) {
            Schema::table('booking_statuses', function (Blueprint $table) {
                $table->boolean('show_in_provider_portal')->default(true)->after('code');
            });
        }

        if (!Schema::hasColumn('emergency_request_statuses', 'show_in_provider_portal')) {
            Schema::table('emergency_request_statuses', function (Blueprint $table) {
                $table->boolean('show_in_provider_portal')->default(true)->after('code');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('booking_statuses', 'show_in_provider_portal')) {
            Schema::table('booking_statuses', function (Blueprint $table) {
                $table->dropColumn('show_in_provider_portal');
            });
        }

        if (Schema::hasColumn('emergency_request_statuses', 'show_in_provider_portal')) {
            Schema::table('emergency_request_statuses', function (Blueprint $table) {
                $table->dropColumn('show_in_provider_portal');
            });
        }
    }
};

