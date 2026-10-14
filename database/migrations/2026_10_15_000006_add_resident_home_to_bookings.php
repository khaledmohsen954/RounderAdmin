<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Keep the exact resident/unit relationship for new orders; legacy rows remain nullable. */
return new class extends Migration {
    public function up(): void {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('resident_home_id')->nullable()->constrained('resident_homes')->restrictOnDelete();
        });
    }
    public function down(): void {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('resident_home_id');
        });
    }
};
