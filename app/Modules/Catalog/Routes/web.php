<?php

use Illuminate\Support\Facades\Route;

// مسارات Catalog فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/packages', [\App\Modules\Catalog\Controllers\Admin\PackageAdminController::class, 'index'])->name('packages.index');
    Route::get('/packages/services/{serviceId}/edit', [\App\Modules\Catalog\Controllers\Admin\PackageAdminController::class, 'editService'])->whereNumber('serviceId')->name('packages.services.edit');
    Route::put('/packages/services/{serviceId}', [\App\Modules\Catalog\Controllers\Admin\PackageAdminController::class, 'updateService'])->whereNumber('serviceId')->name('packages.services.update');
    Route::post('/packages/services', [\App\Modules\Catalog\Controllers\Admin\PackageAdminController::class, 'storeService'])->name('packages.services.store');
    Route::post('/packages', [\App\Modules\Catalog\Controllers\Admin\PackageAdminController::class, 'storePackage'])->name('packages.store');
});
