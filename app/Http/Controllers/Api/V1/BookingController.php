<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Models\Booking; use Illuminate\Http\Request; use Illuminate\Validation\Rule;
class BookingController extends Controller {
 public function index(Request $request){
  return Booking::with(['service.translations','status.translations'])->where('resident_id',$request->user()->id)->latest()->paginate($request->integer('per_page',15));
 }
 public function store(Request $request){
  $data=$request->validate([
   'home_id'=>['required','integer','exists:homes,id',Rule::exists('resident_homes','home_id')->where('user_id',$request->user()->id)],
   'service_id'=>['required','integer','exists:services,id',Rule::exists('services','id')->where('is_active',true)],
   'scheduled_at'=>['required','date','after:now'],
   'notes'=>['nullable','string','max:2000'],
  ]);
  $pending=\App\Models\BookingStatus::where('code','pending')->firstOrFail();
  $service=\App\Models\Service::findOrFail($data['service_id']);
  $booking=Booking::create($data+['resident_id'=>$request->user()->id,'booking_status_id'=>$pending->id,'total_amount'=>$service->base_price]);
  $booking->load(['service.translations','status.translations']);
  return response()->json(['data'=>$booking],201);
 }
}
