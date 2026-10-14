<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\{AboutController,AuthController,DashboardController,LocaleController,UserController,HomeController};
Route::post('/locale',[LocaleController::class,'update'])->name('locale.update');
Route::prefix('admin')->name('admin.')->group(function(){
 Route::middleware('guest')->group(function(){Route::get('/login',[AuthController::class,'show'])->name('login');Route::post('/login',[AuthController::class,'login'])->middleware('throttle:8,1')->name('login.submit');});
 Route::middleware(['auth','admin'])->group(function(){
  Route::get('/',fn()=>redirect()->route('admin.dashboard'));
  Route::get('/dashboard',DashboardController::class)->name('dashboard');
  Route::get('/about',AboutController::class)->name('about');
  Route::get('/users',[UserController::class,'index'])->name('users.index');
  Route::get('/homes',[HomeController::class,'index'])->name('homes.index');
  Route::post('/homes',[HomeController::class,'store'])->name('homes.store');
  Route::post('/users',[UserController::class,'store'])->name('users.store');
  Route::put('/users/{user}',[UserController::class,'update'])->name('users.update');
  Route::put('/users/{user}/password',[UserController::class,'resetPassword'])->name('users.password');
  Route::post('/logout',[AuthController::class,'logout'])->name('logout');
 });
});
Route::redirect('/','/admin/login');
