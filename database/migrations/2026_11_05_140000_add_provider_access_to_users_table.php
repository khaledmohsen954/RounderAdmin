<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('provider_profile_id')->nullable()->after('role_id')
                ->constrained('provider_profiles')->nullOnDelete();
            $table->json('permissions')->nullable()->after('provider_profile_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('provider_profile_id');
            $table->dropColumn('permissions');
        });
    }
};
