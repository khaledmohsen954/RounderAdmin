<?php
namespace App\\Http\\Controllers\\Api\\V1;
use App\\Http\\Controllers\\Controller;
use App\\Models\\Role; use App\\Models\\User;
use Illuminate\\Http\\Request; use Illuminate\\Support\\Facades\\Hash; use Illuminate\\Validation\\ValidationException;
class AuthController extends Controller {
 public function register(Request $request){
  $data=$request->validate([
   'name'=>['required','string','min:2','max:120'],
   'email'=>['required','string','email:rfc,dns','max:254','unique:users,email'],
   'phone'=>['nullable','string','regex:/^\\+[1-9][0-9]{7,14}$/','unique:users,phone'],
   'password'=>['required','string','min:10','max:72','confirmed','regex:/[a-z]/','regex:/[A-Z]/','regex:/[0-9]/'],
   'locale'=>['sometimes','in:ar,en,ur'],
  ]);
  $role=Role::where('code','resident')->firstOrFail();
  $user=User::create($data+['role_id'=>$role->id,'locale'=>$data['locale']??'ar']);
  return response()->json(['data'=>['user'=>$user->only(['id','name','email','phone','locale']),'token'=>$user->createToken('rounder-mobile')->plainTextToken]],201);
 }
 public function login(Request $request){
  $data=$request->validate(['email'=>['required','email:rfc,dns'],'password'=>['required','string','max:72']]);
  $user=User::with('role')->where('email',$data['email'])->first();
  if(!$user||!Hash::check($data['password'],$user->password)||!in_array($user->role->code,['resident','provider'],true))
   throw ValidationException::withMessages(['email'=>__('auth.failed')]);
  return response()->json(['data'=>['user'=>$user->only(['id','name','email','phone','locale']),'token'=>$user->createToken('rounder-mobile')->plainTextToken]]);
 }
 public function logout(Request $request){$request->user()->currentAccessToken()?->delete();return response()->json(['message'=>'Signed out']);}
 public function me(Request $request){return response()->json(['data'=>$request->user()->only(['id','name','email','phone','locale'])]);}
}
