<?php

use Illuminate\Support\Facades\Route;

// مسارات Community فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/content', [\App\Modules\Community\Controllers\Admin\ResidentContentController::class, 'index'])->name('content.index');
    Route::post('/content/announcements', [\App\Modules\Community\Controllers\Admin\ResidentContentController::class, 'storeAnnouncement'])->name('content.announcements.store');
    Route::post('/content/initiatives', [\App\Modules\Community\Controllers\Admin\ResidentContentController::class, 'storeInitiative'])->name('content.initiatives.store');
});
