<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\{AuthController,BookingController,ResidentDashboardController,ResidentHomeController,ResidentJourneyController,ServiceCatalogController};

Route::prefix('v1')->group(function () {
    Route::get('/health', fn () => response()->json(['status' => 'ok', 'app' => 'Rounder']));
    Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:8,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::middleware('resident')->prefix('resident')->group(function () {
            Route::get('/dashboard', ResidentDashboardController::class);
            Route::get('/profile', [ResidentJourneyController::class, 'profile']);
            Route::put('/profile', [ResidentJourneyController::class, 'updateProfile']);
            Route::get('/homes', [ResidentHomeController::class, 'index']);
            Route::post('/homes/link', [ResidentHomeController::class, 'link'])->middleware('throttle:10,1');
            Route::get('/services', ServiceCatalogController::class);
            Route::get('/bookings', [BookingController::class, 'index']);
            Route::post('/bookings', [BookingController::class, 'store'])->middleware('throttle:20,1');
            Route::post('/bookings/{bookingId}/cancel', [BookingController::class, 'cancel'])->middleware('throttle:10,1');
            Route::get('/emergency-requests', [EmergencyRequestController::class, 'index']);
            Route::post('/emergency-requests', [EmergencyRequestController::class, 'store'])->middleware('throttle:3,1');
            Route::delete('/emergency-requests/{requestId}', [EmergencyRequestController::class, 'cancel'])->middleware('throttle:3,1');
            Route::post('/bookings/{bookingId}/rating', [ResidentJourneyController::class, 'rateBooking']);

            Route::get('/association', [ResidentJourneyController::class, 'association']);
            Route::get('/facility-reports', [ResidentJourneyController::class, 'facilityReports']);
            Route::post('/facility-reports', [ResidentJourneyController::class, 'createFacilityReport'])->middleware('throttle:10,1');
            Route::get('/visits', [ResidentJourneyController::class, 'visits']);
            Route::post('/visits', [ResidentJourneyController::class, 'createVisit'])->middleware('throttle:10,1');
            Route::delete('/visits/{visitId}', [ResidentJourneyController::class, 'cancelVisit']);

            Route::get('/announcements', [ResidentJourneyController::class, 'announcements']);
            Route::get('/community/posts', [ResidentJourneyController::class, 'communityPosts']);
            Route::post('/community/posts', [ResidentJourneyController::class, 'createCommunityPost'])->middleware('throttle:10,1');
            Route::post('/community/posts/{postId}/comments', [ResidentJourneyController::class, 'comment'])->middleware('throttle:20,1');
            Route::put('/community/posts/{postId}/reaction', [ResidentJourneyController::class, 'react'])->middleware('throttle:30,1');
            Route::get('/initiatives', [ResidentJourneyController::class, 'initiatives']);
            Route::post('/initiatives/{initiativeId}/participation', [ResidentJourneyController::class, 'joinInitiative']);

            Route::get('/notifications', [ResidentJourneyController::class, 'notifications']);
            Route::put('/notifications/{notificationId}/read', [ResidentJourneyController::class, 'markNotificationRead']);
        });
    });
});
