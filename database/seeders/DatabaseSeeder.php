<?php
namespace Database\Seeders;
use App\Models\{BookingStatus,BookingStatusTranslation,City,CityTranslation,PaymentStatus,Role,RoleTranslation,Service,ServiceCategory,ServiceCategoryTranslation,ServiceTranslation,User};
use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder {
 public function run():void {
  foreach(['admin'=>['ar'=>'مدير النظام','en'=>'Administrator'],'resident'=>['ar'=>'ساكن','en'=>'Resident'],'provider'=>['ar'=>'مقدم خدمة','en'=>'Service provider']] as $code=>$names){
   $role=Role::firstOrCreate(['code'=>$code]); foreach($names as $locale=>$name) RoleTranslation::updateOrCreate(['role_id'=>$role->id,'locale'=>$locale],['name'=>$name]);
  }
  foreach(['pending'=>['ar'=>'قيد الانتظار','en'=>'Pending'],'confirmed'=>['ar'=>'مؤكد','en'=>'Confirmed'],'in_progress'=>['ar'=>'جارٍ التنفيذ','en'=>'In progress'],'completed'=>['ar'=>'مكتمل','en'=>'Completed'],'cancelled'=>['ar'=>'ملغي','en'=>'Cancelled']] as $code=>$names){
   $status=BookingStatus::firstOrCreate(['code'=>$code]); foreach($names as $locale=>$name) BookingStatusTranslation::updateOrCreate(['booking_status_id'=>$status->id,'locale'=>$locale],['name'=>$name]);
  }
  foreach(['pending','paid','failed','refunded'] as $code) PaymentStatus::firstOrCreate(['code'=>$code]);
  $city=City::firstOrCreate(['code'=>'cairo']);
  foreach(['ar'=>'القاهرة','en'=>'Cairo'] as $locale=>$name) CityTranslation::updateOrCreate(['city_id'=>$city->id,'locale'=>$locale],['name'=>$name]);
  $catalog=[
   'maintenance'=>['ar'=>'الصيانة','en'=>'Maintenance','services'=>['plumbing'=>['ar'=>'إصلاح السباكة','en'=>'Plumbing repair','price'=>250],'electrical'=>['ar'=>'إصلاح الكهرباء','en'=>'Electrical repair','price'=>300]]],
   'cleaning'=>['ar'=>'النظافة','en'=>'Cleaning','services'=>['home_cleaning'=>['ar'=>'تنظيف المنزل','en'=>'Home cleaning','price'=>400]]],
  ];
  foreach($catalog as $code=>$categoryData){
   $category=ServiceCategory::firstOrCreate(['code'=>$code],['is_active'=>true]);
   foreach(['ar','en'] as $locale) ServiceCategoryTranslation::updateOrCreate(['service_category_id'=>$category->id,'locale'=>$locale],['name'=>$categoryData[$locale]]);
   foreach($categoryData['services'] as $serviceCode=>$serviceData){
    $service=Service::firstOrCreate(['code'=>$serviceCode],['service_category_id'=>$category->id,'base_price'=>$serviceData['price'],'is_active'=>true]);
    foreach(['ar','en'] as $locale) ServiceTranslation::updateOrCreate(['service_id'=>$service->id,'locale'=>$locale],['name'=>$serviceData[$locale],'description'=>null]);
   }
  }
  $email=env('ROUND_ADMIN_EMAIL'); $password=env('ROUND_ADMIN_PASSWORD');
  if($email&&$password){$admin=Role::where('code','admin')->firstOrFail();User::updateOrCreate(['email'=>$email],['name'=>env('ROUND_ADMIN_NAME','Rounder Admin'),'role_id'=>$admin->id,'locale'=>'ar','password'=>$password]);}
 }
}
