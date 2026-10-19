<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
/** إضافة بيانات المصدر دون إعادة ترقيم المدن أو تغيير مواقع العقارات القائمة. */
return new class extends Migration {
    /** @return void */
    public function up(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->char('country_code', 2)->nullable()->index();
            $table->unsignedInteger('source_city_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unique(['country_code', 'source_city_id']);
        });
    }
    /** @return void */
    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropUnique(['country_code', 'source_city_id']);
            $table->dropIndex(['country_code']);
            $table->dropColumn(['country_code', 'source_city_id', 'is_active']);
        });
    }
};
