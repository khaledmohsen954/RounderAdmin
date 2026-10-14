<?php
namespace Database\Seeders;
use App\Models\EmergencyRequestStatus;
use App\Models\EmergencyRequestStatusTranslation;
use App\Models\EmergencyType;
use App\Models\EmergencyTypeTranslation;
use App\Models\PackageBillingCycle;
use App\Models\ProviderCommissionStatus;
use App\Models\ProviderContractStatus;
use App\Models\SubscriptionStatus;
use App\Models\SubscriptionStatusTranslation;
use App\Models\{BookingStatus,BookingStatusTranslation,City,CityTranslation,PaymentStatus,Role,RoleTranslation,Service,ServiceCategory,ServiceCategoryTranslation,ServiceTranslation,User};
use Illuminate\Database\Seeder;

/** بيانات مرجعية قابلة لإعادة التشغيل؛ لا تنشئ حساب مدير إلا من إعدادات البيئة. */
class DatabaseSeeder extends Seeder {
 public function run():void {
  foreach(['admin'=>['ar'=>'مدير النظام','en'=>'Administrator'],'resident'=>['ar'=>'ساكن','en'=>'Resident'],'provider'=>['ar'=>'مقدم خدمة','en'=>'Service provider']] as $code=>$names){
   $role=Role::firstOrCreate(['code'=>$code]); foreach($names as $locale=>$name) RoleTranslation::updateOrCreate(['role_id'=>$role->id,'locale'=>$locale],['name'=>$name]);
  }
  foreach(['pending'=>['ar'=>'قيد الانتظار','en'=>'Pending','ur'=>'زیرِ التوا'],'confirmed'=>['ar'=>'مؤكد','en'=>'Confirmed','ur'=>'تصدیق شدہ'],'in_progress'=>['ar'=>'جارٍ التنفيذ','en'=>'In progress','ur'=>'جاری ہے'],'completed'=>['ar'=>'مكتمل','en'=>'Completed','ur'=>'مکمل'],'cancelled'=>['ar'=>'ملغي','en'=>'Cancelled','ur'=>'منسوخ']] as $code=>$names){
   $status=BookingStatus::firstOrCreate(['code'=>$code]); foreach($names as $locale=>$name) BookingStatusTranslation::updateOrCreate(['booking_status_id'=>$status->id,'locale'=>$locale],['name'=>$name]);
  }
  foreach(['pending','paid','failed','refunded'] as $code) PaymentStatus::firstOrCreate(['code'=>$code]);
  foreach([
   'received'=>['ar'=>'تم الاستلام','en'=>'Received','ur'=>'موصول'],
   'assigned'=>['ar'=>'تم تعيين فريق','en'=>'Team assigned','ur'=>'ٹیم مقرر'],
   'on_the_way'=>['ar'=>'الفريق في الطريق','en'=>'Team on the way','ur'=>'ٹیم راستے میں'],
   'resolved'=>['ar'=>'تمت المعالجة','en'=>'Resolved','ur'=>'حل ہوگیا'],
   'cancelled'=>['ar'=>'ملغي','en'=>'Cancelled','ur'=>'منسوخ'],
  ] as $code=>$names){
   $status=EmergencyRequestStatus::query()->where('code',$code)->first();
   $statusId=$status?->id ?? EmergencyRequestStatus::query()->create(['code'=>$code,'created_at'=>now(),'updated_at'=>now()])->getKey();
   foreach($names as $locale=>$name) EmergencyRequestStatusTranslation::query()->updateOrCreate(
    ['emergency_request_status_id'=>$statusId,'locale'=>$locale],['name'=>$name,'updated_at'=>now(),'created_at'=>now()]);
  }
  foreach([
   'fire'=>['ar'=>'حريق أو دخان','en'=>'Fire or smoke','ur'=>'آگ یا دھواں'],
   'medical'=>['ar'=>'حالة طبية عاجلة','en'=>'Urgent medical issue','ur'=>'فوری طبی مسئلہ'],
   'gas_leak'=>['ar'=>'اشتباه تسريب غاز','en'=>'Suspected gas leak','ur'=>'گیس کے اخراج کا شبہ'],
   'electrical_hazard'=>['ar'=>'خطر كهربائي','en'=>'Electrical hazard','ur'=>'بجلی کا خطرہ'],
   'water_leak'=>['ar'=>'تسريب مياه شديد','en'=>'Major water leak','ur'=>'پانی کا شدید رساؤ'],
   'elevator'=>['ar'=>'تعطل مصعد مع أشخاص بالداخل','en'=>'Elevator entrapment','ur'=>'لفٹ میں افراد پھنس گئے'],
   'security'=>['ar'=>'مشكلة أمنية عاجلة','en'=>'Urgent security issue','ur'=>'فوری سیکیورٹی مسئلہ'],
  ] as $code=>$names){
   $type=EmergencyType::query()->where('code',$code)->first();
   $typeId=$type?->id ?? EmergencyType::query()->create(['code'=>$code,'is_active'=>true,'created_at'=>now(),'updated_at'=>now()])->getKey();
   foreach($names as $locale=>$name) EmergencyTypeTranslation::query()->updateOrCreate(
    ['emergency_type_id'=>$typeId,'locale'=>$locale],['name'=>$name,'updated_at'=>now(),'created_at'=>now()]);
  }
  $city=City::firstOrCreate(['code'=>'cairo']);
  foreach(['ar'=>'القاهرة','en'=>'Cairo','ur'=>'قاہرہ'] as $locale=>$name) CityTranslation::updateOrCreate(['city_id'=>$city->id,'locale'=>$locale],['name'=>$name]);
  foreach(['monthly'=>1,'quarterly'=>3,'semiannual'=>6,'annual'=>12] as $code=>$months)
   PackageBillingCycle::query()->updateOrCreate(['code'=>$code],['months'=>$months,'updated_at'=>now(),'created_at'=>now()]);
  foreach(['draft','active','expired','terminated'] as $code)
   ProviderContractStatus::query()->updateOrCreate(['code'=>$code],['updated_at'=>now(),'created_at'=>now()]);
  foreach(['pending','settled','reversed'] as $code)
   ProviderCommissionStatus::query()->updateOrCreate(['code'=>$code],['updated_at'=>now(),'created_at'=>now()]);
  foreach(['pending'=>['ar'=>'في انتظار التفعيل','en'=>'Pending','ur'=>'زیر التفعیل'],'active'=>['ar'=>'نشط','en'=>'Active','ur'=>'فعال'],'expired'=>['ar'=>'منتهي','en'=>'Expired','ur'=>'میعاد ختم'],'cancelled'=>['ar'=>'ملغي','en'=>'Cancelled','ur'=>'منسوخ']] as $code=>$names){
   SubscriptionStatus::query()->updateOrCreate(['code'=>$code],['updated_at'=>now(),'created_at'=>now()]);
   $statusId=SubscriptionStatus::query()->where('code',$code)->value('id');
   foreach($names as $locale=>$name) SubscriptionStatusTranslation::query()->updateOrCreate(
    ['subscription_status_id'=>$statusId,'locale'=>$locale],['name'=>$name,'updated_at'=>now(),'created_at'=>now()]);
  }
  $catalog=[
   'maintenance'=>['ar'=>'الصيانة','en'=>'Maintenance','ur'=>'مرمت','services'=>['plumbing'=>['ar'=>'إصلاح السباكة','en'=>'Plumbing repair','ur'=>'پلمبنگ کی مرمت','price'=>250],'electrical'=>['ar'=>'إصلاح الكهرباء','en'=>'Electrical repair','ur'=>'بجلی کی مرمت','price'=>300]]],
   'cleaning'=>['ar'=>'النظافة','en'=>'Cleaning','ur'=>'صفائی','services'=>['home_cleaning'=>['ar'=>'تنظيف المنزل','en'=>'Home cleaning','ur'=>'گھر کی صفائی','price'=>400]]],
  ];
  foreach($catalog as $code=>$categoryData){
   $category=ServiceCategory::firstOrCreate(['code'=>$code],['is_active'=>true]);
   foreach(['ar','en','ur'] as $locale) ServiceCategoryTranslation::updateOrCreate(['service_category_id'=>$category->id,'locale'=>$locale],['name'=>$categoryData[$locale]]);
   foreach($categoryData['services'] as $serviceCode=>$serviceData){
    $service=Service::firstOrCreate(['code'=>$serviceCode],['service_category_id'=>$category->id,'base_price'=>$serviceData['price'],'is_active'=>true]);
    foreach(['ar','en','ur'] as $locale) ServiceTranslation::updateOrCreate(['service_id'=>$service->id,'locale'=>$locale],['name'=>$serviceData[$locale],'description'=>null]);
   }
  }
  $email=env('ROUND_ADMIN_EMAIL'); $password=env('ROUND_ADMIN_PASSWORD');
  if($email&&$password){$admin=Role::where('code','admin')->firstOrFail();User::updateOrCreate(['email'=>$email],['name'=>env('ROUND_ADMIN_NAME','Rounder Admin'),'role_id'=>$admin->id,'locale'=>'ar','password'=>$password]);}
 }
}
