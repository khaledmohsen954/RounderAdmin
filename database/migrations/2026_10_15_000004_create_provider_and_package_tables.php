<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Providers, commission contracts, productized resident plans and subscriptions. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_contract_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->timestamps();
        });
        Schema::create('provider_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('status_id')->constrained('provider_contract_statuses')->restrictOnDelete();
            $table->string('reference', 50)->unique();
            $table->decimal('service_commission_percent', 5, 2)->unsigned();
            $table->decimal('emergency_commission_percent', 5, 2)->unsigned();
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->text('terms')->nullable();
            $table->timestamps();
            $table->index(['provider_profile_id', 'status_id', 'starts_on']);
        });

        Schema::create('provider_commission_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->timestamps();
        });
        Schema::create('provider_commission_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_profile_id')->constrained()->restrictOnDelete();
            $table->foreignId('provider_contract_id')->constrained()->restrictOnDelete();
            $table->foreignId('status_id')->constrained('provider_commission_statuses')->restrictOnDelete();
            $table->foreignId('booking_id')->nullable()->unique()->constrained()->restrictOnDelete();
            $table->foreignId('emergency_request_id')->nullable()->unique()->constrained()->restrictOnDelete();
            $table->decimal('gross_amount', 12, 2)->unsigned();
            $table->decimal('commission_percent_snapshot', 5, 2)->unsigned();
            $table->decimal('commission_amount', 12, 2)->unsigned();
            $table->timestamp('calculated_at');
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
            $table->index(['provider_profile_id', 'status_id', 'calculated_at']);
        });

        Schema::create('emergency_provider_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emergency_request_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('provider_profile_id')->constrained()->restrictOnDelete();
            $table->foreignId('provider_contract_id')->constrained()->restrictOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('provider_note')->nullable();
            $table->timestamps();
        });

        Schema::create('package_billing_cycles', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->unsignedSmallInteger('months');
            $table->timestamps();
        });
        Schema::create('service_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_cycle_id')->constrained('package_billing_cycles')->restrictOnDelete();
            $table->string('code', 60)->unique();
            $table->decimal('price', 12, 2)->unsigned();
            $table->string('currency_code', 3)->default('SAR');
            $table->unsignedSmallInteger('duration_months');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'billing_cycle_id']);
        });
        Schema::create('service_package_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_package_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name', 140);
            $table->text('description')->nullable();
            $table->unique(['service_package_id', 'locale']);
            $table->timestamps();
        });
        Schema::create('service_package_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('included_quantity')->nullable();
            $table->boolean('is_unlimited')->default(false);
            $table->timestamps();
            $table->unique(['service_package_id', 'service_id']);
        });

        Schema::create('subscription_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->timestamps();
        });
        Schema::create('subscription_status_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_status_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name', 80);
            $table->unique(['subscription_status_id', 'locale']);
            $table->timestamps();
        });
        Schema::create('resident_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_home_id')->constrained()->restrictOnDelete();
            $table->foreignId('service_package_id')->constrained()->restrictOnDelete();
            $table->foreignId('status_id')->constrained('subscription_statuses')->restrictOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference', 50)->unique();
            $table->decimal('price_snapshot', 12, 2)->unsigned();
            $table->string('currency_code', 3)->default('SAR');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->index(['resident_home_id', 'status_id', 'ends_at']);
        });
        Schema::create('resident_subscription_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('included_quantity')->nullable();
            $table->unsignedSmallInteger('used_quantity')->default(0);
            $table->boolean('is_unlimited')->default(false);
            $table->timestamps();
            $table->unique(['resident_subscription_id', 'service_id']);
        });
    }

    public function down(): void
    {
        foreach ([
            'resident_subscription_services', 'resident_subscriptions', 'subscription_status_translations',
            'subscription_statuses', 'service_package_services', 'service_package_translations',
            'service_packages', 'package_billing_cycles', 'emergency_provider_assignments',
            'provider_commission_entries', 'provider_commission_statuses', 'provider_contracts',
            'provider_contract_statuses',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
