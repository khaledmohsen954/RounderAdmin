<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Modules\Identity\Models\{Admin, User};
use Illuminate\Support\Facades\DB;
/** فصل المصادقة دون حذف أصحاب السجلات التاريخية في users. */
return new class extends Migration {
    /** @return void @throws \Throwable */
    public function up(): void
    {
        Schema::create('permission_sets', function (Blueprint $t) {
            $t->id(); $t->string('name_ar',120); $t->string('name_en',120);
            $t->json('permissions'); $t->timestamps();
        });
        Schema::create('admins', function (Blueprint $t) {
            $t->id(); $t->string('name',120); $t->string('email',254)->unique();
            $t->string('phone',20)->nullable()->unique(); $t->string('locale',5)->default('ar');
            $t->string('password'); $t->rememberToken(); $t->boolean('is_active')->default(true);
            $t->boolean('is_super_admin')->default(false);
            $t->foreignId('permission_set_id')->nullable()->constrained()->restrictOnDelete();
            $t->timestamps();
        });
        DB::transaction(function () {
            User::whereHas('role', fn ($q)=>$q->where('code', \App\Modules\Identity\Enums\RoleCode::Admin->value))
                ->chunkById(100, function ($users) {
                    foreach ($users as $user) {
                        Admin::forceCreate(['id'=>$user->id,'name'=>$user->name,'email'=>$user->email,'phone'=>$user->phone,
                            'locale'=>$user->locale,'password'=>$user->password,'is_active'=>true,'is_super_admin'=>true]);
                        // لا حذف للسجل القديم: سجلات الطلبات تشير إليه. تُلغى قدرته على الدخول.
                        $user->tokens()->delete();
                        $user->forceFill(['password'=>\Illuminate\Support\Str::random(64),'remember_token'=>null])->save();
                    }
                });
        });
    }
    /** @return void */
    public function down(): void
    {
        Schema::dropIfExists('admins'); Schema::dropIfExists('permission_sets');
    }
};
