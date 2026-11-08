<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['service_id', 'is_active', 'sort_order']);
        });

        Schema::create('sub_service_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_service_id')->constrained('sub_services')->cascadeOnDelete();
            $table->string('locale', 8);
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['sub_service_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_service_translations');
        Schema::dropIfExists('sub_services');
    }
};
