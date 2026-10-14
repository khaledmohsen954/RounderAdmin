<?php

use Illuminate\Support\Facades\Route;

// مسارات Properties فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/homes', [\App\Modules\Properties\Controllers\Admin\HomeController::class, 'index'])->name('homes.index');
    Route::post('/homes', [\App\Modules\Properties\Controllers\Admin\HomeController::class, 'store'])->name('homes.store');
});
