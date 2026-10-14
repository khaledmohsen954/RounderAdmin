<?php

use Illuminate\Support\Facades\Route;

// مسارات Facilities فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.
Route::prefix('v1/resident')->middleware(['auth:sanctum', 'resident'])->group(function () {
    Route::get('/facility-reports', [\App\Modules\Facilities\Controllers\Api\FacilityController::class, 'facilityReports']);
    Route::post('/facility-reports', [\App\Modules\Facilities\Controllers\Api\FacilityController::class, 'createFacilityReport'])->middleware('throttle:10,1');
});
