<?php

use Illuminate\Support\Facades\Route;

// مسارات Finance فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.
Route::prefix('v1/resident')->middleware(['auth:sanctum', 'resident'])->group(function () {
    Route::get('/association', [\App\Modules\Finance\Controllers\Api\AssociationController::class, 'association']);
});
