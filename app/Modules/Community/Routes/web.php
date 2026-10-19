<?php

use Illuminate\Support\Facades\Route;

// مسارات Community فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/content', [\App\Modules\Community\Controllers\Admin\ResidentContentController::class, 'index'])->name('content.index');
    Route::post('/content/announcements', [\App\Modules\Community\Controllers\Admin\ResidentContentController::class, 'storeAnnouncement'])->name('content.announcements.store');
    Route::post('/content/initiatives', [\App\Modules\Community\Controllers\Admin\ResidentContentController::class, 'storeInitiative'])->name('content.initiatives.store');
});

Route::prefix('admin')->name('admin.')->middleware(['auth','admin'])->group(function () {
    Route::delete('/content/announcements/{id}', [\App\Modules\Community\Controllers\Admin\DeletionController::class, 'announcement'])->whereNumber('id')->name('content.announcements.destroy');
    Route::delete('/content/initiatives/{id}', [\App\Modules\Community\Controllers\Admin\DeletionController::class, 'initiative'])->whereNumber('id')->name('content.initiatives.destroy');
});

Route::prefix('admin/content')->name('admin.content.')->middleware(['auth','admin'])->group(function () {
    Route::get('/announcements/{id}/edit',[\App\Modules\Community\Controllers\Admin\ContentEditingController::class,'announcement'])->whereNumber('id')->name('announcements.edit');
    Route::put('/announcements/{id}',[\App\Modules\Community\Controllers\Admin\ContentEditingController::class,'updateAnnouncement'])->whereNumber('id')->name('announcements.update');
    Route::get('/initiatives/{id}/edit',[\App\Modules\Community\Controllers\Admin\ContentEditingController::class,'initiative'])->whereNumber('id')->name('initiatives.edit');
    Route::put('/initiatives/{id}',[\App\Modules\Community\Controllers\Admin\ContentEditingController::class,'updateInitiative'])->whereNumber('id')->name('initiatives.update');
});
