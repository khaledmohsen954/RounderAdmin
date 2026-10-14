<?php

use Illuminate\Support\Facades\Route;

// مسارات Emergencies فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.
Route::prefix('v1/resident')->middleware(['auth:sanctum', 'resident'])->group(function () {
    Route::get('/emergency-requests', [\App\Modules\Emergencies\Controllers\Api\EmergencyRequestController::class, 'index']);
    Route::post('/emergency-requests', [\App\Modules\Emergencies\Controllers\Api\EmergencyRequestController::class, 'store'])->middleware('throttle:3,1');
    Route::delete('/emergency-requests/{requestId}', [\App\Modules\Emergencies\Controllers\Api\EmergencyRequestController::class, 'cancel'])->middleware('throttle:3,1');
});
