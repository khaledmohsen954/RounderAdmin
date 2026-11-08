<?php

use App\Modules\Support\Controllers\Api\SupportApiController;
use Illuminate\Support\Facades\Route;

// جلب معلومات التواصل متاح عاماً ومتاح للساكن
Route::prefix('v1')->group(function () {
    Route::get('/support/info', [SupportApiController::class, 'info']);
});

// مسارات رسائل وتذاكر الدعم للساكن المصادق عليه
Route::prefix('v1/resident/support')->middleware(['auth:sanctum', 'resident'])->group(function () {
    Route::get('/info', [SupportApiController::class, 'info']);
    Route::get('/tickets', [SupportApiController::class, 'index']);
    Route::post('/tickets', [SupportApiController::class, 'store']);
    Route::get('/tickets/{id}', [SupportApiController::class, 'show'])->whereNumber('id');
});

