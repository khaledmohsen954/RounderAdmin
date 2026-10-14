<?php

use Illuminate\Support\Facades\Route;

// مسارات Providers فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/providers', [\App\Modules\Providers\Controllers\Admin\ProviderController::class, 'index'])->name('providers.index');
    Route::post('/providers', [\App\Modules\Providers\Controllers\Admin\ProviderController::class, 'store'])->name('providers.store');
});
