<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Models\Booking; use Illuminate\Http\Request;
class ResidentDashboardController extends Controller {
 public function __invoke(Request $request){
  $user=$request->user(); $home=$user->residentHomes()->with('city.translations')->wherePivot('is_primary',true)->first() ?? $user->residentHomes()->with('city.translations')->first();
  abort_unless($home,404,'No residence is linked to this account.');
  $locale=$request->getLocale();
  $upcoming=Booking::with(['service.translations','status.translations'])->where('resident_id',$user->id)->where('home_id',$home->id)->whereHas('status',fn($q)=>$q->whereIn('code',['confirmed','in_progress']))->where('scheduled_at','>=',now())->orderBy('scheduled_at')->first();
  $latest=Booking::with(['service.translations','status.translations'])->where('resident_id',$user->id)->where('home_id',$home->id)->latest()->first();
  $present=fn($booking)=>$booking?['title'=>$booking->service->translations->firstWhere('locale',$locale)?->name ?? $booking->service->code,'subtitle'=>$booking->scheduled_at->translatedFormat('d M Y')]:null;
  return response()->json(['data'=>['resident_name'=>$user->name,'home_name'=>$home->name.' · '.$home->pivot->unit_label,'association_balance'=>(float)$home->association_balance,'property_health_percent'=>$home->health_percent,'upcoming_service'=>$present($upcoming),'recent_activity'=>$present($latest)]]);
 }
}
