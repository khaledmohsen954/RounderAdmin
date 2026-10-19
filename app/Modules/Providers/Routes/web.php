<?php

use Illuminate\Support\Facades\Route;

// مسارات Providers فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/providers', [\App\Modules\Providers\Controllers\Admin\ProviderController::class, 'index'])->name('providers.index');
    Route::post('/providers', [\App\Modules\Providers\Controllers\Admin\ProviderController::class, 'store'])->name('providers.store');
    Route::get('/providers/create', [\App\Modules\Providers\Controllers\Admin\ProviderController::class, 'create'])->name('providers.create');
    Route::get('/providers/{provider}/edit', [\App\Modules\Providers\Controllers\Admin\ProviderController::class, 'edit'])->whereNumber('provider')->name('providers.edit');
    Route::put('/providers/{provider}', [\App\Modules\Providers\Controllers\Admin\ProviderController::class, 'update'])->whereNumber('provider')->name('providers.update');
});

Route::prefix('admin')->name('admin.')->middleware(['auth','admin'])->group(function () {
    Route::delete('/providers/{id}', [\App\Modules\Providers\Controllers\Admin\DeletionController::class, 'provider'])->whereNumber('id')->name('providers.destroy');
});
