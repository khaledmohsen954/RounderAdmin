<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/** إضافة بيانات العرض دون تغيير أسعار الاشتراكات والطلبات السابقة. */
return new class extends Migration
{
    /** @return void */
    public function up(): void
    {
        foreach (['services' => 'base_price', 'service_packages' => 'price'] as $table => $price) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->decimal('original_price', 12, 2)->nullable();
                $blueprint->decimal('price_before_discount', 12, 2)->nullable();
                $blueprint->string('discount_type', 20)->default('none');
                $blueprint->decimal('discount_value', 12, 2)->default(0);
                $blueprint->string('image_path')->nullable();
            });
            // ترحيل دفعي لمرة واحدة؛ SQL مستقل عن نماذج التطبيق المتغيرة عبر الإصدارات.
            DB::statement("UPDATE `{$table}` SET price_before_discount = `{$price}`");
        }
    }

    /** @return void */
    public function down(): void
    {
        foreach (['services', 'service_packages'] as $table) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropColumn([
                'original_price', 'price_before_discount', 'discount_type', 'discount_value', 'image_path',
            ]));
        }
    }
};
