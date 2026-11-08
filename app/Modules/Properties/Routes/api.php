<?php

use Illuminate\Support\Facades\Route;

// مسارات Properties فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.
Route::prefix('v1/resident')->middleware(['auth:sanctum', 'resident'])->group(function () {
    Route::get('/cities', \App\Modules\Properties\Controllers\Api\CityController::class);
    Route::get('/homes', [\App\Modules\Properties\Controllers\Api\ResidentHomeController::class, 'index']);
    Route::post('/homes', [\App\Modules\Properties\Controllers\Api\ResidentHomeController::class, 'addUnit'])->middleware('throttle:10,1');
    Route::post('/homes/add-unit', [\App\Modules\Properties\Controllers\Api\ResidentHomeController::class, 'addUnit'])->middleware('throttle:10,1');
    Route::post('/homes/set-primary', [\App\Modules\Properties\Controllers\Api\ResidentHomeController::class, 'setPrimary'])->middleware('throttle:10,1');
    Route::post('/homes/link', [\App\Modules\Properties\Controllers\Api\ResidentHomeController::class, 'link'])->middleware('throttle:10,1');

    // حذف السكن أو الوحدة للساكن
    Route::delete('/homes/{id}', [\App\Modules\Properties\Controllers\Api\ResidentHomeController::class, 'deleteUnit'])->middleware('throttle:10,1');

    // مسارات العقارات (Properties) - إضافة العقار وربطه بالمستخدم وعرض عقاراته
    Route::get('/properties', [\App\Modules\Properties\Controllers\Api\ResidentHomeController::class, 'index']);
    Route::post('/properties', [\App\Modules\Properties\Controllers\Api\ResidentHomeController::class, 'addProperty'])->middleware('throttle:10,1');
    Route::post('/properties/add', [\App\Modules\Properties\Controllers\Api\ResidentHomeController::class, 'addProperty'])->middleware('throttle:10,1');
    Route::post('/properties/link', [\App\Modules\Properties\Controllers\Api\ResidentHomeController::class, 'link'])->middleware('throttle:10,1');

    Route::get('/visits', [\App\Modules\Properties\Controllers\Api\VisitController::class, 'visits']);
    Route::post('/visits', [\App\Modules\Properties\Controllers\Api\VisitController::class, 'createVisit'])->middleware('throttle:10,1');
    Route::delete('/visits/{visitId}', [\App\Modules\Properties\Controllers\Api\VisitController::class, 'cancelVisit']);
});

Route::prefix('v1')->middleware(['auth:sanctum', 'resident'])->group(function () {
    Route::get('/properties', [\App\Modules\Properties\Controllers\Api\ResidentHomeController::class, 'index']);
    Route::post('/properties', [\App\Modules\Properties\Controllers\Api\ResidentHomeController::class, 'addProperty'])->middleware('throttle:10,1');
});
