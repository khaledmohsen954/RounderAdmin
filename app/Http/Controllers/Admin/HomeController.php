<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller; use App\Models\City; use App\Models\Home; use Illuminate\Http\Request;
/** إدارة المباني والمدن وربطها بالسكان. */
class HomeController extends Controller {
 public function index(){ $homes=Home::with(['city.translations','residents'])->latest()->paginate(15);$cities=City::with('translations')->orderBy('code')->get();return view('admin.homes.index',compact('homes','cities')); }
 public function store(Request $request){
  $data=$request->validate(['city_id'=>['required','integer','exists:cities,id'],'name'=>['required','string','min:2','max:120'],'address'=>['required','string','max:255'],'property_code'=>['required','alpha_dash','min:5','max:60','unique:homes,property_code'],'health_percent'=>['required','integer','between:0,100'],'association_balance'=>['required','numeric','min:0','max:9999999999.99']]);
  Home::create($data);return back()->with('status',__('Home created successfully.'));
 }
}
