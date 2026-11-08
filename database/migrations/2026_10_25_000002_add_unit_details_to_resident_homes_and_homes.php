<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resident_homes', function (Blueprint $table) {
            if (!Schema::hasColumn('resident_homes', 'residence_type')) {
                $table->string('residence_type', 50)->nullable()->after('unit_label');
            }
            if (!Schema::hasColumn('resident_homes', 'custom_name')) {
                $table->string('custom_name', 120)->nullable()->after('residence_type');
            }
            if (!Schema::hasColumn('resident_homes', 'floor_number')) {
                $table->string('floor_number', 40)->nullable()->after('custom_name');
            }
            if (!Schema::hasColumn('resident_homes', 'area_sqm')) {
                $table->decimal('area_sqm', 10, 2)->nullable()->after('floor_number');
            }
            if (!Schema::hasColumn('resident_homes', 'ownership_type')) {
                $table->string('ownership_type', 40)->default('owner')->after('area_sqm');
            }
        });

        Schema::table('homes', function (Blueprint $table) {
            if (!Schema::hasColumn('homes', 'district')) {
                $table->string('district', 120)->nullable()->after('address');
            }
            if (!Schema::hasColumn('homes', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('district');
            }
            if (!Schema::hasColumn('homes', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('resident_homes', function (Blueprint $table) {
            $table->dropColumn(['residence_type', 'custom_name', 'floor_number', 'area_sqm', 'ownership_type']);
        });

        Schema::table('homes', function (Blueprint $table) {
            $table->dropColumn(['district', 'latitude', 'longitude']);
        });
    }
};
