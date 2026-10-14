<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller; use App\Models\Role; use App\Models\User;
use Illuminate\Http\Request; use Illuminate\Validation\Rule;
/** إدارة حسابات الدخول والأدوار وتغيير كلمات المرور. */
class UserController extends Controller {
 public function index(Request $request){
  $users=User::with('role.translations')->when($request->filled('q'),fn($q)=>$q->where(fn($w)=>$w->where('name','like','%'.$request->q.'%')->orWhere('email','like','%'.$request->q.'%')->orWhere('phone','like','%'.$request->q.'%')))
   ->when($request->filled('role'),fn($q)=>$q->whereHas('role',fn($r)=>$r->where('code',$request->role)))->latest()->paginate(15)->withQueryString();
  $roles=Role::with('translations')->orderBy('code')->get();
  return view('admin.users.index',compact('users','roles'));
 }
 public function store(Request $request){
  $data=$request->validate(['name'=>['required','string','min:2','max:120'],'email'=>['required','email:rfc,dns','max:254','unique:users,email'],'phone'=>['nullable','regex:/^\+[1-9][0-9]{7,14}$/','unique:users,phone'],'role_id'=>['required','integer','exists:roles,id'],'locale'=>['required','in:ar,en,ur'],'password'=>['required','string','min:12','max:72','confirmed','regex:/[a-z]/','regex:/[A-Z]/','regex:/[0-9]/']]);
  User::create($data);
  return back()->with('status',__('User created successfully.'));
 }
 public function update(Request $request,User $user){
  $data=$request->validate(['name'=>['sometimes','required','string','min:2','max:120'],'email'=>['sometimes','required','email:rfc,dns','max:254',Rule::unique('users','email')->ignore($user->id)],'phone'=>['sometimes','nullable','regex:/^\+[1-9][0-9]{7,14}$/',Rule::unique('users','phone')->ignore($user->id)],'role_id'=>['sometimes','required','integer','exists:roles,id'],'locale'=>['sometimes','required','in:ar,en,ur']]);
  abort_if($request->user()->is($user),422,__('You cannot change your own account here.'));
  $user->update($data); return back()->with('status',__('User updated successfully.'));
 }
 public function resetPassword(Request $request,User $user){
  $data=$request->validate(['password'=>['required','string','min:12','max:72','confirmed','regex:/[a-z]/','regex:/[A-Z]/','regex:/[0-9]/']]);
  $user->update(['password'=>$data['password']]); $user->tokens()->delete();
  return back()->with('status',__('Password updated and active app sessions revoked.'));
 }
}
