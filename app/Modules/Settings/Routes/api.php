<?php

use Illuminate\Support\Facades\Route;

// مسارات الصفحات الثابتة (الشروط والأحكام، سياسة الخصوصية) - عامة لجميع الواجهات وتطبيقات الجوال.
Route::prefix('v1')->group(function () {
    Route::get('/terms', [\App\Modules\Settings\Controllers\Api\PageController::class, 'terms']);
    Route::get('/privacy', [\App\Modules\Settings\Controllers\Api\PageController::class, 'privacy']);
    Route::get('/pages/{slug}', [\App\Modules\Settings\Controllers\Api\PageController::class, 'show']);

    Route::prefix('resident')->group(function () {
        Route::get('/terms', [\App\Modules\Settings\Controllers\Api\PageController::class, 'terms']);
        Route::get('/privacy', [\App\Modules\Settings\Controllers\Api\PageController::class, 'privacy']);
        Route::get('/pages/{slug}', [\App\Modules\Settings\Controllers\Api\PageController::class, 'show']);
    });
});

