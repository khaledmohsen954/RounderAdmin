<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
/** تعريفات المقارنة مشتركة بالمعرف؛ القيمة تابعة لباقة محددة. */
return new class extends Migration {
    /** @return void */
    public function up(): void
    {
        Schema::create('package_features', function (Blueprint $table) {
            $table->id();
            $table->string('code',60)->unique();
            $table->string('name_ar',160);
            $table->string('name_en',160);
            $table->string('name_ur',160);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('package_feature_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_feature_id')->constrained()->restrictOnDelete();
            $table->string('availability',20);
            $table->string('value_ar',500)->nullable();
            $table->string('value_en',500)->nullable();
            $table->string('value_ur',500)->nullable();
            $table->timestamps();
            $table->unique(['service_package_id','package_feature_id'],'package_feature_pair_unique');
        });
    }
    /** @return void */
    public function down(): void
    {
        Schema::dropIfExists('package_feature_values');
        Schema::dropIfExists('package_features');
    }
};
