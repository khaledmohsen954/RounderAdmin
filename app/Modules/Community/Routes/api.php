<?php

use Illuminate\Support\Facades\Route;

// مسارات Community فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.
Route::prefix('v1/resident')->middleware(['auth:sanctum', 'resident'])->group(function () {
    Route::get('/announcements', [\App\Modules\Community\Controllers\Api\CommunityController::class, 'announcements']);
    Route::get('/community/posts', [\App\Modules\Community\Controllers\Api\CommunityController::class, 'communityPosts']);
    Route::post('/community/posts', [\App\Modules\Community\Controllers\Api\CommunityController::class, 'createCommunityPost'])->middleware('throttle:10,1');
    Route::post('/community/posts/{postId}/comments', [\App\Modules\Community\Controllers\Api\CommunityController::class, 'comment'])->middleware('throttle:20,1');
    Route::put('/community/posts/{postId}/reaction', [\App\Modules\Community\Controllers\Api\CommunityController::class, 'react'])->middleware('throttle:30,1');
    Route::get('/initiatives', [\App\Modules\Community\Controllers\Api\CommunityController::class, 'initiatives']);
    Route::post('/initiatives/{initiativeId}/participation', [\App\Modules\Community\Controllers\Api\CommunityController::class, 'joinInitiative']);
});
