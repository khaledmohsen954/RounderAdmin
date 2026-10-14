<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Resident app journeys: association finance, community, facilities, notifications,
 * booking evidence, ratings and after-care. Lookup values use referenced IDs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('association_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('currency_code', 3)->default('SAR');
            $table->timestamps();
        });

        Schema::create('association_dues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('association_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resident_home_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 40)->unique();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('amount', 12, 2)->unsigned();
            $table->date('due_at');
            $table->foreignId('payment_status_id')->constrained()->restrictOnDelete();
            $table->timestamps();
            $table->unique(['resident_home_id', 'period_start', 'period_end']);
            $table->index(['payment_status_id', 'due_at']);
        });

        Schema::create('association_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('association_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resident_home_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('entry_type', 24);
            $table->string('reference', 60)->unique();
            $table->decimal('amount', 12, 2)->unsigned();
            $table->text('description')->nullable();
            $table->timestamp('posted_at');
            $table->timestamps();
            $table->index(['association_account_id', 'posted_at']);
        });

        Schema::create('facility_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('facility_category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_category_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name', 120);
            $table->unique(['facility_category_id', 'locale']);
            $table->timestamps();
        });
        Schema::create('facility_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_id')->constrained()->restrictOnDelete();
            $table->foreignId('resident_home_id')->constrained()->restrictOnDelete();
            $table->foreignId('facility_category_id')->constrained()->restrictOnDelete();
            $table->foreignId('status_id')->constrained('booking_statuses')->restrictOnDelete();
            $table->string('reference', 40)->unique();
            $table->string('title', 160);
            $table->text('description');
            $table->string('location', 160)->nullable();
            $table->timestamp('reported_at');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['home_id', 'status_id', 'reported_at']);
        });
        Schema::create('facility_report_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->string('disk', 40)->default('public');
            $table->string('path', 500);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->timestamps();
        });
        Schema::create('facility_report_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('status_id')->constrained('booking_statuses')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('resident_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_home_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('visitor_name', 120);
            $table->string('visitor_phone', 25)->nullable();
            $table->string('access_code', 12)->unique();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->timestamps();
            $table->index(['resident_home_id', 'starts_at']);
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('reference', 40)->unique();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
        Schema::create('announcement_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title', 180);
            $table->longText('body');
            $table->unique(['announcement_id', 'locale']);
            $table->timestamps();
        });

        Schema::create('community_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resident_home_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('reference', 40)->unique();
            $table->text('body');
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();
            $table->index(['home_id', 'created_at']);
        });
        Schema::create('community_post_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_post_id')->constrained()->cascadeOnDelete();
            $table->string('disk', 40)->default('public');
            $table->string('path', 500);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->timestamps();
        });
        Schema::create('community_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('community_comments')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
            $table->index(['community_post_id', 'created_at']);
        });
        Schema::create('community_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reaction_code', 24);
            $table->timestamps();
            $table->unique(['community_post_id', 'user_id']);
        });

        Schema::create('initiatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('reference', 40)->unique();
            $table->string('title', 180);
            $table->text('description');
            $table->decimal('target_amount', 12, 2)->unsigned()->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->index(['home_id', 'is_published', 'starts_at']);
        });
        Schema::create('initiative_participations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('initiative_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resident_home_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('pledged_amount', 12, 2)->unsigned()->default(0);
            $table->timestamp('joined_at');
            $table->timestamps();
            $table->unique(['initiative_id', 'resident_home_id']);
        });

        Schema::create('notification_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->timestamps();
        });
        Schema::create('notification_type_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_type_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name', 100);
            $table->unique(['notification_type_id', 'locale']);
            $table->timestamps();
        });
        Schema::create('resident_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('notification_type_id')->constrained()->restrictOnDelete();
            $table->string('reference_type', 80)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('title', 180);
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'read_at', 'created_at']);
        });

        Schema::create('booking_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->string('kind', 32);
            $table->string('disk', 40)->default('public');
            $table->string('path', 500);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->timestamps();
        });
        Schema::create('booking_additional_work', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('users')->restrictOnDelete();
            $table->string('reference', 40)->unique();
            $table->text('description');
            $table->decimal('amount', 12, 2)->unsigned();
            $table->foreignId('status_id')->constrained('booking_statuses')->restrictOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
        Schema::create('booking_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('resident_id')->constrained('users')->restrictOnDelete();
            $table->unsignedTinyInteger('score');
            $table->text('comment')->nullable();
            $table->timestamps();
        });
        Schema::create('booking_warranties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('users')->restrictOnDelete();
            $table->string('reference', 40)->unique();
            $table->text('terms');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'booking_warranties', 'booking_ratings', 'booking_additional_work', 'booking_media',
            'resident_notifications', 'notification_type_translations', 'notification_types',
            'initiative_participations', 'initiatives', 'community_reactions', 'community_comments',
            'community_post_media', 'community_posts', 'announcement_translations', 'announcements',
            'resident_visits', 'facility_report_updates', 'facility_report_media', 'facility_reports',
            'facility_category_translations', 'facility_categories', 'association_ledger_entries',
            'association_dues', 'association_accounts',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
