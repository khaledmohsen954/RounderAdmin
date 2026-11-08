<?php

use Illuminate\Support\Facades\Route;

// مسارات Bookings فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.
Route::prefix('v1/resident')->middleware(['auth:sanctum', 'resident'])->group(function () {
    Route::get('/booking-statuses', [\App\Modules\Bookings\Controllers\Api\BookingController::class, 'statuses']);
    Route::get('/bookings', [\App\Modules\Bookings\Controllers\Api\BookingController::class, 'index']);
    Route::get('/bookings/{bookingId}', [\App\Modules\Bookings\Controllers\Api\BookingController::class, 'show'])->whereNumber('bookingId');
    Route::post('/bookings', [\App\Modules\Bookings\Controllers\Api\BookingController::class, 'store'])->middleware('throttle:20,1');
    Route::post('/bookings/{bookingId}/cancel', [\App\Modules\Bookings\Controllers\Api\BookingController::class, 'cancel'])->middleware('throttle:10,1');
    Route::get('/bookings/{bookingId}/rating', [\App\Modules\Bookings\Controllers\Api\BookingRatingController::class, 'show'])->whereNumber('bookingId');
    Route::post('/bookings/{bookingId}/rating', [\App\Modules\Bookings\Controllers\Api\BookingRatingController::class, 'rateBooking'])->whereNumber('bookingId');
    Route::get('/service-providers', [\App\Modules\Bookings\Controllers\Api\BookingController::class, 'providers']);
});
