<?php

use Illuminate\Support\Facades\Route;

// مسارات Bookings فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/service-orders/create', [\App\Modules\Bookings\Controllers\Admin\OrderCreationController::class, 'serviceForm'])->name('service-orders.create');
    Route::post('/service-orders', [\App\Modules\Bookings\Controllers\Admin\OrderCreationController::class, 'storeService'])->name('service-orders.store');
    Route::get('/service-orders', [\App\Modules\Bookings\Controllers\Admin\ServiceOrderAdminController::class, 'index'])->name('service-orders.index');
    Route::put('/service-orders/{bookingId}', [\App\Modules\Bookings\Controllers\Admin\ServiceOrderAdminController::class, 'update'])->name('service-orders.update');
});

Route::prefix('admin')->name('admin.')->middleware(['auth','admin'])->group(function () {
    Route::delete('/service-orders/{id}', [\App\Modules\Bookings\Controllers\Admin\DeletionController::class, 'booking'])->whereNumber('id')->name('service-orders.destroy');
});
