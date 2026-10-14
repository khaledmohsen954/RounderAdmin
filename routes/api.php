<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\{AuthController,BookingController,ResidentDashboardController,ResidentHomeController,ServiceCatalogController};
Route::prefix('v1')->group(function(){
 Route::get('/health',fn()=>response()->json(['status'=>'ok','app'=>'Rounder']));
 Route::post('/auth/register',[AuthController::class,'register'])->middleware('throttle:5,1');
 Route::post('/auth/login',[AuthController::class,'login'])->middleware('throttle:8,1');
 Route::middleware('auth:sanctum')->group(function(){
  Route::get('/me',[AuthController::class,'me']);
  Route::post('/auth/logout',[AuthController::class,'logout']);
  Route::middleware('resident')->prefix('resident')->group(function(){
   Route::get('/dashboard',ResidentDashboardController::class);
   Route::get('/homes',[ResidentHomeController::class,'index']);
   Route::post('/homes/link',[ResidentHomeController::class,'link'])->middleware('throttle:10,1');
   Route::get('/services',ServiceCatalogController::class);
   Route::get('/bookings',[BookingController::class,'index']);
   Route::post('/bookings',[BookingController::class,'store'])->middleware('throttle:20,1');
   Route::post('/bookings/{bookingId}/cancel',[BookingController::class,'cancel'])->middleware('throttle:10,1');
  });
 });
});
