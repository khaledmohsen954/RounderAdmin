<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->char('country_code', 2)->default('SA')->index();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('region_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();
            $table->string('locale', 8);
            $table->string('name');
            $table->timestamps();

            $table->unique(['region_id', 'locale']);
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->foreignId('region_id')->nullable()->after('country_code')->constrained('regions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('region_id');
        });

        Schema::dropIfExists('region_translations');
        Schema::dropIfExists('regions');
    }
};

