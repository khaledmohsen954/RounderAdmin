<?php

use Illuminate\Support\Facades\Route;

// مسارات Identity فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.
Route::prefix('v1')->group(function () {
    Route::post('/auth/register', [\App\Modules\Identity\Controllers\Api\AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/auth/login', [\App\Modules\Identity\Controllers\Api\AuthController::class, 'login'])->middleware('throttle:8,1');
});
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/me', [\App\Modules\Identity\Controllers\Api\AuthController::class, 'me']);
    Route::post('/auth/logout', [\App\Modules\Identity\Controllers\Api\AuthController::class, 'logout']);
});
Route::prefix('v1/resident')->middleware(['auth:sanctum', 'resident'])->group(function () {
    Route::get('/profile', [\App\Modules\Identity\Controllers\Api\ProfileController::class, 'profile']);
    Route::put('/profile', [\App\Modules\Identity\Controllers\Api\ProfileController::class, 'updateProfile']);
});
