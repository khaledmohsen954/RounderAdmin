<?php

namespace App\Modules\Dashboard\Services;
use App\Modules\Bookings\Enums\BookingStatusCode;

use App\Modules\Bookings\Models\Booking;
use App\Modules\Identity\Models\User;

/** قواعد عمل Dashboard؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class ApiResidentDashboardService
{

    /**
     * قراءة بيانات المجال مع الحفاظ على قيود الوصول.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __invoke(array $input, ?User $actor): mixed
    {
        $user=$actor; $home=$user->residentHomes()->with('city.translations')->wherePivot('is_primary',true)->first() ?? $user->residentHomes()->with('city.translations')->first();
  if(!$home) return ['data'=>['has_home'=>false,'resident_name'=>$user->name,'home_id'=>null,'home_name'=>'','association_balance'=>0,'property_health_percent'=>0,'upcoming_service'=>null,'recent_activity'=>null]];
  $locale=app()->getLocale();
  $upcoming=Booking::with(['service.translations','status.translations'])->where('resident_id',$user->id)->where('home_id',$home->id)->whereHas('status',fn($q)=>$q->whereIn('code',[BookingStatusCode::Confirmed->value,BookingStatusCode::InProgress->value]))->where('scheduled_at','>=',now())->orderBy('scheduled_at')->first();
  $latest=Booking::with(['service.translations','status.translations'])->where('resident_id',$user->id)->where('home_id',$home->id)->latest()->first();
  $present=fn($booking)=>$booking?['title'=>$booking->service->translations->firstWhere('locale',$locale)?->name ?? $booking->service->code,'subtitle'=>$booking->scheduled_at->translatedFormat('d M Y')]:null;
  return ['data'=>['has_home'=>true,'resident_name'=>$user->name,'home_id'=>$home->id,'home_name'=>$home->name.' · '.$home->pivot->unit_label,'association_balance'=>(float)$home->association_balance,'property_health_percent'=>$home->health_percent,'upcoming_service'=>$present($upcoming),'recent_activity'=>$present($latest)]];
    }
}
