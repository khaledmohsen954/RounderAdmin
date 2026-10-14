<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller; use App\Models\Home; use Illuminate\Http\JsonResponse; use Illuminate\Http\Request; use Illuminate\Support\Facades\DB; use Illuminate\Validation\Rule;
/** ربط الحساب بوحدة وتحديد الوحدة الأساسية داخل معاملة. */
class ResidentHomeController extends Controller {
 public function index(Request $request):JsonResponse {
  $homes=$request->user()->residentHomes()->with('city.translations')->get()->map(fn(Home $h)=>['id'=>$h->id,'name'=>$h->name,'unit_label'=>$h->pivot->unit_label,'is_primary'=>(bool)$h->pivot->is_primary,'address'=>$h->address,'property_code'=>$h->property_code]);
  return response()->json(['data'=>$homes]);
 }
 public function link(Request $request):JsonResponse {
  $data=$request->validate(['property_code'=>['required','string','alpha_dash','exists:homes,property_code'],'unit_label'=>['required','string','min:1','max:40']]);
  $user=$request->user();$home=Home::where('property_code',$data['property_code'])->firstOrFail();
  $already=$user->residentHomes()->where('homes.id',$home->id)->wherePivot('unit_label',$data['unit_label'])->exists();
  if($already)return response()->json(['message'=>__('Residence is already linked to this account.')],409);
  DB::transaction(function()use($user,$home,$data){
   $user->residentHomes()->newPivotStatement()->where('user_id',$user->id)->update(['is_primary'=>false]);
   $user->residentHomes()->attach($home->id,['unit_label'=>$data['unit_label'],'is_primary'=>true]);
  });
  return response()->json(['message'=>__('Residence linked successfully.'),'data'=>['home_id'=>$home->id,'name'=>$home->name,'unit_label'=>$data['unit_label']]],201);
 }
}
