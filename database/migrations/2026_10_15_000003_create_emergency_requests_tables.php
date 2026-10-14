<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Emergency response requests stay separate from scheduled service bookings. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergency_request_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->timestamps();
        });

        Schema::create('emergency_request_status_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emergency_request_status_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name', 100);
            $table->unique(['emergency_request_status_id', 'locale']);
            $table->timestamps();
        });

        Schema::create('emergency_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('emergency_type_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emergency_type_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name', 120);
            $table->unique(['emergency_type_id', 'locale']);
            $table->timestamps();
        });

        Schema::create('emergency_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('resident_home_id')->constrained()->restrictOnDelete();
            $table->foreignId('home_id')->constrained()->restrictOnDelete();
            $table->foreignId('emergency_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('status_id')->constrained('emergency_request_statuses')->restrictOnDelete();
            $table->string('reference', 40)->unique();
            $table->string('contact_phone', 25);
            $table->string('location_details', 255);
            $table->text('description');
            $table->timestamp('requested_at');
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['status_id', 'requested_at']);
            $table->index(['home_id', 'requested_at']);
            $table->index(['resident_id', 'requested_at']);
        });

        Schema::create('emergency_request_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emergency_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('status_id')->constrained('emergency_request_statuses')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        foreach ([
            'emergency_request_updates', 'emergency_requests',
            'emergency_request_status_translations', 'emergency_request_statuses',\n            'emergency_type_translations', 'emergency_types',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
