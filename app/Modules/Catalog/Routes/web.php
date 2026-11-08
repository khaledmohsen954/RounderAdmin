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


Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    $c = \App\Modules\Catalog\Controllers\Admin\SubServiceAdminController::class;

    Route::get('/sub-services', [$c, 'index'])->name('sub-services.index');
    Route::get('/sub-services/create', [$c, 'create'])->name('sub-services.create');
    Route::post('/sub-services', [$c, 'store'])->name('sub-services.store');
    Route::get('/sub-services/{subServiceId}/edit', [$c, 'edit'])->whereNumber('subServiceId')->name('sub-services.edit');
    Route::put('/sub-services/{subServiceId}', [$c, 'update'])->whereNumber('subServiceId')->name('sub-services.update');
    Route::delete('/sub-services/{subServiceId}', [$c, 'destroy'])->whereNumber('subServiceId')->name('sub-services.destroy');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    $cat = \App\Modules\Catalog\Controllers\Admin\ServiceCategoryAdminController::class;

    Route::get('/service-categories', [$cat, 'index'])->name('service-categories.index');
    Route::get('/service-categories/create', [$cat, 'create'])->name('service-categories.create');
    Route::post('/service-categories', [$cat, 'store'])->name('service-categories.store');
    Route::get('/service-categories/{categoryId}/edit', [$cat, 'edit'])->whereNumber('categoryId')->name('service-categories.edit');
    Route::put('/service-categories/{categoryId}', [$cat, 'update'])->whereNumber('categoryId')->name('service-categories.update');
    Route::delete('/service-categories/{categoryId}', [$cat, 'destroy'])->whereNumber('categoryId')->name('service-categories.destroy');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    $p = \App\Modules\Catalog\Controllers\Admin\PriceAdminController::class;

    Route::get('/prices', [$p, 'index'])->name('prices.index');
    Route::get('/prices/create', [$p, 'create'])->name('prices.create');
    Route::post('/prices', [$p, 'store'])->name('prices.store');
    Route::get('/prices/{priceId}/edit', [$p, 'edit'])->whereNumber('priceId')->name('prices.edit');
    Route::put('/prices/{priceId}', [$p, 'update'])->whereNumber('priceId')->name('prices.update');
    Route::post('/prices/{priceId}/review', [$p, 'review'])->whereNumber('priceId')->name('prices.review');
    Route::delete('/prices/{priceId}', [$p, 'destroy'])->whereNumber('priceId')->name('prices.destroy');

    Route::get('/prices/ajax/provider-services/{providerId}', [$p, 'getProviderServices'])->whereNumber('providerId')->name('prices.ajax.provider-services');
    Route::get('/prices/ajax/sub-services/{serviceId}', [$p, 'getSubServices'])->whereNumber('serviceId')->name('prices.ajax.sub-services');
    Route::get('/prices/ajax/region-cities/{regionId}', [$p, 'getRegionCities'])->whereNumber('regionId')->name('prices.ajax.region-cities');
    Route::get('/prices/{priceId}/cities', [$p, 'getPriceCities'])->whereNumber('priceId')->name('prices.cities');
});
