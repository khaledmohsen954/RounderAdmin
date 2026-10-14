<?php
use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;
return new class extends Migration {
 public function up():void {
  Schema::create('roles',function(Blueprint $t){$t->id();$t->string('code',40)->unique();$t->timestamps();});
  Schema::create('role_translations',function(Blueprint $t){$t->id();$t->foreignId('role_id')->constrained()->cascadeOnDelete();$t->string('locale',5);$t->string('name');$t->unique(['role_id','locale']);$t->timestamps();});
  Schema::create('users',function(Blueprint $t){$t->id();$t->foreignId('role_id')->constrained()->restrictOnDelete();$t->string('name',120);$t->string('email')->unique();$t->string('phone',25)->nullable()->unique();$t->string('locale',5)->default('ar');$t->timestamp('email_verified_at')->nullable();$t->string('password');$t->rememberToken();$t->timestamps();});
  Schema::create('cities',function(Blueprint $t){$t->id();$t->string('code',40)->unique();$t->timestamps();});
  Schema::create('city_translations',function(Blueprint $t){$t->id();$t->foreignId('city_id')->constrained()->cascadeOnDelete();$t->string('locale',5);$t->string('name',120);$t->unique(['city_id','locale']);$t->timestamps();});
  Schema::create('homes',function(Blueprint $t){$t->id();$t->foreignId('city_id')->constrained()->restrictOnDelete();$t->string('name',120);$t->string('address',255);$t->string('property_code',60)->unique();$t->unsignedTinyInteger('health_percent')->default(100);$t->decimal('association_balance',12,2)->default(0);$t->timestamps();});
  Schema::create('resident_homes',function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained()->cascadeOnDelete();$t->foreignId('home_id')->constrained()->cascadeOnDelete();$t->string('unit_label',40);$t->boolean('is_primary')->default(false);$t->timestamps();$t->unique(['user_id','home_id','unit_label']);});
  Schema::create('booking_statuses',function(Blueprint $t){$t->id();$t->string('code',40)->unique();$t->timestamps();});
  Schema::create('booking_status_translations',function(Blueprint $t){$t->id();$t->foreignId('booking_status_id')->constrained()->cascadeOnDelete();$t->string('locale',5);$t->string('name',80);$t->unique(['booking_status_id','locale']);$t->timestamps();});
  Schema::create('service_categories',function(Blueprint $t){$t->id();$t->string('code',60)->unique();$t->boolean('is_active')->default(true);$t->timestamps();});
  Schema::create('service_category_translations',function(Blueprint $t){$t->id();$t->foreignId('service_category_id')->constrained()->cascadeOnDelete();$t->string('locale',5);$t->string('name',120);$t->unique(['service_category_id','locale']);$t->timestamps();});
  Schema::create('services',function(Blueprint $t){$t->id();$t->foreignId('service_category_id')->constrained()->restrictOnDelete();$t->string('code',60)->unique();$t->decimal('base_price',12,2)->unsigned();$t->boolean('is_active')->default(true);$t->timestamps();});
  Schema::create('service_translations',function(Blueprint $t){$t->id();$t->foreignId('service_id')->constrained()->cascadeOnDelete();$t->string('locale',5);$t->string('name',160);$t->text('description')->nullable();$t->unique(['service_id','locale']);$t->timestamps();});
  Schema::create('provider_profiles',function(Blueprint $t){$t->id();$t->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();$t->string('company_name',160);$t->boolean('is_approved')->default(false);$t->timestamps();});
  Schema::create('bookings',function(Blueprint $t){$t->id();$t->foreignId('resident_id')->constrained('users')->restrictOnDelete();$t->foreignId('home_id')->constrained()->restrictOnDelete();$t->foreignId('service_id')->constrained()->restrictOnDelete();$t->foreignId('provider_id')->nullable()->constrained('users')->nullOnDelete();$t->foreignId('booking_status_id')->constrained()->restrictOnDelete();$t->dateTime('scheduled_at');$t->decimal('total_amount',12,2)->unsigned();$t->text('notes')->nullable();$t->timestamps();$t->index(['booking_status_id','scheduled_at']);});
  Schema::create('payment_statuses',function(Blueprint $t){$t->id();$t->string('code',40)->unique();$t->timestamps();});
  Schema::create('payments',function(Blueprint $t){$t->id();$t->foreignId('booking_id')->constrained()->cascadeOnDelete();$t->foreignId('payment_status_id')->constrained()->restrictOnDelete();$t->decimal('amount',12,2)->unsigned();$t->string('reference',100)->nullable()->unique();$t->timestamps();});
  Schema::create('booking_status_history',function(Blueprint $t){$t->id();$t->foreignId('booking_id')->constrained()->cascadeOnDelete();$t->foreignId('booking_status_id')->constrained()->restrictOnDelete();$t->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();$t->text('note')->nullable();$t->timestamp('created_at')->useCurrent();});
 }
 public function down():void {
  foreach(['booking_status_history','payments','payment_statuses','bookings','provider_profiles','service_translations','services','service_category_translations','service_categories','booking_status_translations','booking_statuses','resident_homes','homes','city_translations','cities','users','role_translations','roles'] as $table) Schema::dropIfExists($table);
 }
};
