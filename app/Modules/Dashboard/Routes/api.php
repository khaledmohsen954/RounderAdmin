<?php

use Illuminate\Support\Facades\Route;

// مسارات Dashboard فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.
Route::prefix('v1/resident')->middleware(['auth:sanctum', 'resident'])->group(function () {
    Route::get('/dashboard', \App\Modules\Dashboard\Controllers\Api\ResidentDashboardController::class);
});
