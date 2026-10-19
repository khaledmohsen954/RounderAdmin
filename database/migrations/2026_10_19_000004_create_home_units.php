<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
/** تفاصيل المبنى والشقق منفصلة؛ nullable يحافظ على العقارات القديمة دون اختراع قياسات. */
return new class extends Migration {
    /** @return void */
    public function up(): void
    {
        Schema::table('homes', fn (Blueprint $t) => $t->unsignedSmallInteger('floors_count')->nullable());
        Schema::create('home_units', function (Blueprint $t) {
            $t->id(); $t->foreignId('home_id')->constrained()->cascadeOnDelete();
            $t->string('unit_number',40); $t->unsignedSmallInteger('floor_number');
            $t->decimal('area_sqm',10,2); $t->unsignedTinyInteger('rooms_count'); $t->timestamps();
            $t->unique(['home_id','unit_number']);
        });
        Schema::table('resident_homes', fn (Blueprint $t) =>
            $t->foreignId('home_unit_id')->nullable()->constrained()->restrictOnDelete());
    }
    /** @return void */
    public function down(): void
    {
        Schema::table('resident_homes', fn (Blueprint $t) => $t->dropConstrainedForeignId('home_unit_id'));
        Schema::dropIfExists('home_units');
        Schema::table('homes', fn (Blueprint $t) => $t->dropColumn('floors_count'));
    }
};
