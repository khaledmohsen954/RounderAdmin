<?php

use Illuminate\Support\Facades\Route;

// مسارات Catalog فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.
Route::prefix('v1/resident')->middleware(['auth:sanctum', 'resident'])->group(function () {
    Route::get('/service-categories', \App\Modules\Catalog\Controllers\Api\ServiceCategoryController::class);
    Route::get('/services', \App\Modules\Catalog\Controllers\Api\ServiceCatalogController::class);
    Route::get('/packages', [\App\Modules\Catalog\Controllers\Api\ResidentPackageController::class, 'index']);
    Route::post('/subscriptions', [\App\Modules\Catalog\Controllers\Api\ResidentPackageController::class, 'subscribe'])->middleware('throttle:5,1');
    Route::delete('/subscriptions/{subscriptionId}', [\App\Modules\Catalog\Controllers\Api\ResidentPackageController::class, 'cancel']);
$c = \App\Modules\Catalog\Controllers\Api\SubServiceCatalogController::class;

// GET /sub-services?service_id=1
Route::get('/sub-services', $c)->name('api.sub-services.index');

// GET /services/1/sub-services
Route::get('/services/{serviceId}/sub-services', $c)->whereNumber('serviceId')->name('api.services.sub-services');
});
