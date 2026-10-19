<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
/** Durable dashboard events with independent read state for each administrator. */
return new class extends Migration {
    /** @return void */
    public function up(): void {
        Schema::create('admin_alerts', function (Blueprint $table) {
            $table->id(); $table->string('type'); $table->unsignedBigInteger('subject_id');
            $table->timestamps(); $table->index(['type','created_at']);
        });
        Schema::create('admin_alert_reads', function (Blueprint $table) {
            $table->id(); $table->foreignId('admin_alert_id')->constrained('admin_alerts')->cascadeOnDelete();
            $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete();
            $table->timestamp('read_at'); $table->unique(['admin_alert_id','admin_id']);
        });
    }
    /** @return void */
    public function down(): void { Schema::dropIfExists('admin_alert_reads'); Schema::dropIfExists('admin_alerts'); }
};
