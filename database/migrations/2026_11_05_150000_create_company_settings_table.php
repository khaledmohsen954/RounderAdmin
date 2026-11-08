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
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('trade_name', 191)->comment('الاسم التجاري للمنشأة');
            $table->string('cr_number', 100)->comment('رقم السجل التجاري');
            $table->string('tax_number', 100)->nullable()->comment('الرقم الضريبي (اختياري)');
            $table->string('trademark_registration', 191)->nullable()->comment('تسجيل العلامة التجارية');
            $table->string('legal_representative', 191)->comment('اسم الممثل القانوني');
            $table->string('representative_title', 191)->comment('صفة الممثل القانوني (مثل: المدير العام / المفوض بالتوقيع)');
            $table->string('stamp_path', 255)->nullable()->comment('مسار صورة الختم الرسمي للمنشأة');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};

