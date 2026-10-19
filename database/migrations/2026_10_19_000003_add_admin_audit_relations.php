<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
/** مفاتيح منفصلة لصاحب العملية الإداري؛ لا نخلط أرقام admins و users. */
return new class extends Migration {
    /** @return void */
    public function up(): void
    {
        Schema::table('booking_status_history', function (Blueprint $t) {
            $t->unsignedBigInteger('changed_by')->nullable()->change();
            $t->foreignId('admin_changed_by')->nullable()->constrained('admins')->restrictOnDelete();
        });
        Schema::table('booking_provider_assignments', function (Blueprint $t) {
            $t->unsignedBigInteger('assigned_by')->nullable()->change();
            $t->foreignId('admin_assigned_by')->nullable()->constrained('admins')->restrictOnDelete();
        });
        Schema::table('emergency_provider_assignments', function (Blueprint $t) {
            $t->unsignedBigInteger('assigned_by')->nullable()->change();
            $t->foreignId('admin_assigned_by')->nullable()->constrained('admins')->restrictOnDelete();
        });
        Schema::table('emergency_requests', function (Blueprint $t) {
            $t->unsignedBigInteger('handled_by')->nullable()->change();
            $t->foreignId('admin_handled_by')->nullable()->constrained('admins')->restrictOnDelete();
        });
        Schema::table('emergency_request_updates', function (Blueprint $t) {
            $t->unsignedBigInteger('updated_by')->nullable()->change();
            $t->foreignId('admin_updated_by')->nullable()->constrained('admins')->restrictOnDelete();
        });
        Schema::table('facility_report_updates', function (Blueprint $t) {
            $t->unsignedBigInteger('updated_by')->nullable()->change();
            $t->foreignId('admin_updated_by')->nullable()->constrained('admins')->restrictOnDelete();
        });
        Schema::table('announcements', function (Blueprint $t) {
            $t->unsignedBigInteger('created_by')->nullable()->change();
            $t->foreignId('admin_created_by')->nullable()->constrained('admins')->restrictOnDelete();
        });
        Schema::table('initiatives', function (Blueprint $t) {
            $t->unsignedBigInteger('created_by')->nullable()->change();
            $t->foreignId('admin_created_by')->nullable()->constrained('admins')->restrictOnDelete();
        });
    }
    /** @return void */
    public function down(): void
    {
        Schema::table('initiatives', fn (Blueprint $t) => $t->dropConstrainedForeignId('admin_created_by'));
        Schema::table('announcements', fn (Blueprint $t) => $t->dropConstrainedForeignId('admin_created_by'));
        Schema::table('facility_report_updates', fn (Blueprint $t) => $t->dropConstrainedForeignId('admin_updated_by'));
        Schema::table('emergency_request_updates', fn (Blueprint $t) => $t->dropConstrainedForeignId('admin_updated_by'));
        Schema::table('emergency_requests', fn (Blueprint $t) => $t->dropConstrainedForeignId('admin_handled_by'));
        Schema::table('emergency_provider_assignments', fn (Blueprint $t) => $t->dropConstrainedForeignId('admin_assigned_by'));
        Schema::table('booking_provider_assignments', fn (Blueprint $t) => $t->dropConstrainedForeignId('admin_assigned_by'));
        Schema::table('booking_status_history', fn (Blueprint $t) => $t->dropConstrainedForeignId('admin_changed_by'));
    }
};
