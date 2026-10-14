<?php

use Illuminate\Support\Facades\Route;

// مسارات Dashboard فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', \App\Modules\Dashboard\Controllers\Admin\DashboardController::class)->name('dashboard');
    Route::get('/about', \App\Modules\Dashboard\Controllers\Admin\AboutController::class)->name('about');
});
