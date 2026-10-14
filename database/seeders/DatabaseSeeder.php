<?php
namespace Database\\Seeders;
use App\\Models\\{BookingStatus,BookingStatusTranslation,PaymentStatus,Role,RoleTranslation,User};
use Illuminate\\Database\\Seeder;
class DatabaseSeeder extends Seeder {
 public function run():void {
  foreach(['admin'=>['ar'=>'مدير النظام','en'=>'Administrator'],'resident'=>['ar'=>'ساكن','en'=>'Resident'],'provider'=>['ar'=>'مقدم خدمة','en'=>'Service provider']] as $code=>$names){
   $role=Role::firstOrCreate(['code'=>$code]); foreach($names as $locale=>$name) RoleTranslation::updateOrCreate(['role_id'=>$role->id,'locale'=>$locale],['name'=>$name]);
  }
  foreach(['pending'=>['ar'=>'قيد الانتظار','en'=>'Pending'],'confirmed'=>['ar'=>'مؤكد','en'=>'Confirmed'],'in_progress'=>['ar'=>'جارٍ التنفيذ','en'=>'In progress'],'completed'=>['ar'=>'مكتمل','en'=>'Completed'],'cancelled'=>['ar'=>'ملغي','en'=>'Cancelled']] as $code=>$names){
   $status=BookingStatus::firstOrCreate(['code'=>$code]); foreach($names as $locale=>$name) BookingStatusTranslation::updateOrCreate(['booking_status_id'=>$status->id,'locale'=>$locale],['name'=>$name]);
  }
  foreach(['pending','paid','failed','refunded'] as $code) PaymentStatus::firstOrCreate(['code'=>$code]);
  $email=env('ROUND_ADMIN_EMAIL'); $password=env('ROUND_ADMIN_PASSWORD');
  if($email&&$password){$admin=Role::where('code','admin')->firstOrFail();User::updateOrCreate(['email'=>$email],['name'=>env('ROUND_ADMIN_NAME','Rounder Admin'),'role_id'=>$admin->id,'locale'=>'ar','password'=>$password]);}
 }
}
