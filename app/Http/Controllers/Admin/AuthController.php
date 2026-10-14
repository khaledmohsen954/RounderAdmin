<?php
namespace App\\Http\\Controllers\\Admin;
use App\\Http\\Controllers\\Controller;
use Illuminate\\Http\\Request; use Illuminate\\Support\\Facades\\Auth; use Illuminate\\Validation\\ValidationException;
class AuthController extends Controller {
 public function show(){return view('admin.login');}
 public function login(Request $request){
  $data=$request->validate(['email'=>['required','email:rfc,dns','max:254'],'password'=>['required','string','max:200']]);
  if(!Auth::attempt($data,$request->boolean('remember')) || Auth::user()->role?->code!=='admin'){
   Auth::logout(); throw ValidationException::withMessages(['email'=>__('auth.failed')]);
  }
  $request->session()->regenerate(); return redirect()->intended(route('admin.dashboard'));
 }
 public function logout(Request $request){Auth::logout();$request->session()->invalidate();$request->session()->regenerateToken();return redirect()->route('admin.login');}
}
