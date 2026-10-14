<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Models\Booking; use App\Models\BookingStatus; use Illuminate\Http\Request; use Illuminate\Support\Facades\DB; use Illuminate\Validation\Rule;
class BookingController extends Controller {
 public function index(Request $request){
  return Booking::with(['home','service.translations','status.translations'])->where('resident_id',$request->user()->id)->latest()->paginate(min($request->integer('per_page',15),50));
 }
 public function store(Request $request){
  $data=$request->validate([
   'home_id'=>['required','integer','exists:homes,id',Rule::exists('resident_homes','home_id')->where('user_id',$request->user()->id)],
   'service_id'=>['required','integer',Rule::exists('services','id')->where('is_active',true)],
   'scheduled_at'=>['required','date','after:now'],'notes'=>['nullable','string','max:2000'],
  ]);
  $pending=BookingStatus::where('code','pending')->firstOrFail();$service=\App\Models\Service::findOrFail($data['service_id']);
  $booking=Booking::create($data+['resident_id'=>$request->user()->id,'booking_status_id'=>$pending->id,'total_amount'=>$service->base_price]);
  DB::table('booking_status_history')->insert(['booking_id'=>$booking->id,'booking_status_id'=>$pending->id,'changed_by'=>$request->user()->id,'note'=>'Created by resident','created_at'=>now()]);
  $booking->load(['service.translations','status.translations']);
  return response()->json(['data'=>$booking],201);
 }
 public function cancel(Request $request,int $bookingId){
  $data=$request->validate(['reason'=>['required','string','min:3','max:500']]);
  $booking=Booking::where('resident_id',$request->user()->id)->findOrFail($bookingId);
  abort_unless(in_array($booking->status()->value('code'),['pending','confirmed'],true),409,__('This booking can no longer be cancelled.'));
  $cancelled=BookingStatus::where('code','cancelled')->firstOrFail();
  DB::transaction(function()use($booking,$cancelled,$request,$data){
   $booking->update(['booking_status_id'=>$cancelled->id,'notes'=>trim(($booking->notes? $booking->notes."\n":'').'Cancellation: '.$data['reason'])]);
   DB::table('booking_status_history')->insert(['booking_id'=>$booking->id,'booking_status_id'=>$cancelled->id,'changed_by'=>$request->user()->id,'note'=>$data['reason'],'created_at'=>now()]);
  });
  return response()->json(['data'=>$booking->fresh()->load(['status.translations'])]);
 }
}
