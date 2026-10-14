<?php

use Illuminate\Support\Facades\Route;

// مسارات Catalog فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.
Route::prefix('v1/resident')->middleware(['auth:sanctum', 'resident'])->group(function () {
    Route::get('/services', \App\Modules\Catalog\Controllers\Api\ServiceCatalogController::class);
    Route::get('/packages', [\App\Modules\Catalog\Controllers\Api\ResidentPackageController::class, 'index']);
    Route::post('/subscriptions', [\App\Modules\Catalog\Controllers\Api\ResidentPackageController::class, 'subscribe'])->middleware('throttle:5,1');
    Route::delete('/subscriptions/{subscriptionId}', [\App\Modules\Catalog\Controllers\Api\ResidentPackageController::class, 'cancel']);
});
