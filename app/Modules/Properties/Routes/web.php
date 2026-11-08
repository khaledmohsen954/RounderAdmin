<?php

use Illuminate\Support\Facades\Route;

// مسارات Properties فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/realestate', [\App\Modules\Properties\Controllers\Admin\HomeController::class, 'index'])->name('realestate.index');
    Route::post('/realestate', [\App\Modules\Properties\Controllers\Admin\HomeController::class, 'store'])->name('realestate.store');
    Route::get('/realestate/add', [\App\Modules\Properties\Controllers\Admin\HomeController::class, 'create'])->name('realestate.create');
    Route::get('/realestate/{homeId}/edit', [\App\Modules\Properties\Controllers\Admin\HomeController::class, 'edit'])->whereNumber('homeId')->name('realestate.edit');
    Route::put('/realestate/{homeId}', [\App\Modules\Properties\Controllers\Admin\HomeController::class, 'update'])->whereNumber('homeId')->name('realestate.update');
    Route::delete('/realestate/{homeId}', [\App\Modules\Properties\Controllers\Admin\HomeController::class, 'destroy'])->whereNumber('homeId')->name('realestate.destroy');

    // المناطق
    Route::get('/regions', [\App\Modules\Properties\Controllers\Admin\RegionAdminController::class, 'index'])->name('regions.index');
    Route::get('/regions/create', [\App\Modules\Properties\Controllers\Admin\RegionAdminController::class, 'create'])->name('regions.create');
    Route::post('/regions', [\App\Modules\Properties\Controllers\Admin\RegionAdminController::class, 'store'])->name('regions.store');
    Route::get('/regions/{regionId}/edit', [\App\Modules\Properties\Controllers\Admin\RegionAdminController::class, 'edit'])->whereNumber('regionId')->name('regions.edit');
    Route::put('/regions/{regionId}', [\App\Modules\Properties\Controllers\Admin\RegionAdminController::class, 'update'])->whereNumber('regionId')->name('regions.update');
    Route::delete('/regions/{regionId}', [\App\Modules\Properties\Controllers\Admin\RegionAdminController::class, 'destroy'])->whereNumber('regionId')->name('regions.destroy');

    // المدن
    Route::get('/cities', [\App\Modules\Properties\Controllers\Admin\CityAdminController::class, 'index'])->name('cities.index');
    Route::get('/cities/create', [\App\Modules\Properties\Controllers\Admin\CityAdminController::class, 'create'])->name('cities.create');
    Route::post('/cities', [\App\Modules\Properties\Controllers\Admin\CityAdminController::class, 'store'])->name('cities.store');
    Route::get('/cities/{cityId}/edit', [\App\Modules\Properties\Controllers\Admin\CityAdminController::class, 'edit'])->whereNumber('cityId')->name('cities.edit');
    Route::put('/cities/{cityId}', [\App\Modules\Properties\Controllers\Admin\CityAdminController::class, 'update'])->whereNumber('cityId')->name('cities.update');
    Route::delete('/cities/{cityId}', [\App\Modules\Properties\Controllers\Admin\CityAdminController::class, 'destroy'])->whereNumber('cityId')->name('cities.destroy');
});
