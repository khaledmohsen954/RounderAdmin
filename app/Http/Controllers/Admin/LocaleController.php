<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
/** حفظ لغة لوحة الإدارة ضمن اللغات المدعومة. */
class LocaleController extends Controller {
 public function update(Request $request){
  $data=$request->validate(['locale'=>['required','in:ar,en']]);
  $request->session()->put('locale',$data['locale']);
  if($request->user()) $request->user()->update(['locale'=>$data['locale']]);
  return back();
 }
}
