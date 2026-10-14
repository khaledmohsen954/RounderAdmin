<?php
use Illuminate\\Support\\Facades\\Route;
use App\\Http\\Controllers\\Admin\\{AboutController,AuthController,DashboardController,LocaleController};
Route::post('/locale',[LocaleController::class,'update'])->name('locale.update');
Route::prefix('admin')->name('admin.')->group(function(){
 Route::middleware('guest')->group(function(){Route::get('/login',[AuthController::class,'show'])->name('login');Route::post('/login',[AuthController::class,'login'])->name('login.submit');});
 Route::middleware('auth')->group(function(){
  Route::get('/',fn()=>redirect()->route('admin.dashboard'));
  Route::get('/dashboard',DashboardController::class)->name('dashboard');
  Route::get('/about',AboutController::class)->name('about');
  Route::post('/logout',[AuthController::class,'logout'])->name('logout');
 });
});
Route::redirect('/','/admin/login');
