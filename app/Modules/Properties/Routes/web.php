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
});
