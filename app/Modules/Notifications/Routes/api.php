<?php

use Illuminate\Support\Facades\Route;

// مسارات Notifications فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.
Route::prefix('v1/resident')->middleware(['auth:sanctum', 'resident'])->group(function () {
    Route::get('/notifications', [\App\Modules\Notifications\Controllers\Api\NotificationController::class, 'notifications']);
    Route::put('/notifications/{notificationId}/read', [\App\Modules\Notifications\Controllers\Api\NotificationController::class, 'markNotificationRead']);
});
