<?php

use Illuminate\Support\Facades\Route;

// مسارات Catalog فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/packages/create', [\App\Modules\Catalog\Controllers\Admin\PackageAdminController::class, 'createPackage'])->name('packages.create');
    Route::get('/services', [\App\Modules\Catalog\Controllers\Admin\PackageAdminController::class, 'services'])->name('services.index');
    Route::get('/package-features', [\App\Modules\Catalog\Controllers\Admin\PackageAdminController::class, 'definitions'])->name('package-features.index');
    Route::get('/package-features/create', [\App\Modules\Catalog\Controllers\Admin\PackageAdminController::class, 'createDefinition'])->name('package-features.create');
    Route::get('/subscriptions', [\App\Modules\Catalog\Controllers\Admin\PackageAdminController::class, 'subscriptions'])->name('subscriptions.index');
    Route::get('/packages', [\App\Modules\Catalog\Controllers\Admin\PackageAdminController::class, 'index'])->name('packages.index');
    Route::get('/packages/services/{serviceId}/edit', [\App\Modules\Catalog\Controllers\Admin\PackageAdminController::class, 'editService'])->whereNumber('serviceId')->name('packages.services.edit');
    Route::put('/packages/services/{serviceId}', [\App\Modules\Catalog\Controllers\Admin\PackageAdminController::class, 'updateService'])->whereNumber('serviceId')->name('packages.services.update');
    Route::post('/packages/services', [\App\Modules\Catalog\Controllers\Admin\PackageAdminController::class, 'storeService'])->name('packages.services.store');
    Route::post('/packages', [\App\Modules\Catalog\Controllers\Admin\PackageAdminController::class, 'storePackage'])->name('packages.store');
    Route::get('/packages/{packageId}/edit', [\App\Modules\Catalog\Controllers\Admin\PackageAdminController::class, 'editPackage'])->whereNumber('packageId')->name('packages.edit');
    Route::put('/packages/{packageId}', [\App\Modules\Catalog\Controllers\Admin\PackageAdminController::class, 'updatePackage'])->whereNumber('packageId')->name('packages.update');
    Route::post('/package-features', [\App\Modules\Catalog\Controllers\Admin\PackageFeatureController::class,'store'])->name('package-features.store');
    Route::put('/package-features/{featureId}', [\App\Modules\Catalog\Controllers\Admin\PackageFeatureController::class,'update'])->whereNumber('featureId')->name('package-features.update');
    Route::get('/services/create', [\App\Modules\Catalog\Controllers\Admin\PackageAdminController::class, 'createService'])->name('services.create');
});

Route::prefix('admin')->name('admin.')->middleware(['auth','admin'])->group(function () {
    Route::delete('/packages/services/{id}', [\App\Modules\Catalog\Controllers\Admin\DeletionController::class, 'service'])->whereNumber('id')->name('packages.services.destroy');
    Route::delete('/packages/{id}', [\App\Modules\Catalog\Controllers\Admin\DeletionController::class, 'package'])->whereNumber('id')->name('packages.destroy');
    Route::delete('/package-features/{id}', [\App\Modules\Catalog\Controllers\Admin\DeletionController::class, 'feature'])->whereNumber('id')->name('package-features.destroy');
});
