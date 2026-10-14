<?php
use Illuminate\\Support\\Facades\\Route;
use App\\Http\\Controllers\\Api\\V1\\{BookingController,ResidentDashboardController};
Route::prefix('v1')->group(function(){
 Route::get('/health',fn()=>response()->json(['status'=>'ok','app'=>'Rounder']));
 Route::middleware('auth:sanctum')->group(function(){
  Route::get('/resident/dashboard',ResidentDashboardController::class);
  Route::get('/resident/bookings',[BookingController::class,'index']);
  Route::post('/resident/bookings',[BookingController::class,'store'])->middleware('throttle:20,1');
 });
});
