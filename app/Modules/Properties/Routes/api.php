<?php

use Illuminate\Support\Facades\Route;

// مسارات Properties فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.
Route::prefix('v1/resident')->middleware(['auth:sanctum', 'resident'])->group(function () {
    Route::get('/homes', [\App\Modules\Properties\Controllers\Api\ResidentHomeController::class, 'index']);
    Route::post('/homes/link', [\App\Modules\Properties\Controllers\Api\ResidentHomeController::class, 'link'])->middleware('throttle:10,1');
    Route::get('/visits', [\App\Modules\Properties\Controllers\Api\VisitController::class, 'visits']);
    Route::post('/visits', [\App\Modules\Properties\Controllers\Api\VisitController::class, 'createVisit'])->middleware('throttle:10,1');
    Route::delete('/visits/{visitId}', [\App\Modules\Properties\Controllers\Api\VisitController::class, 'cancelVisit']);
});
