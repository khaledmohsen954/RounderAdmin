<?php

use Illuminate\Support\Facades\Route;

// مسارات Emergencies فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/emergencies/create', [\App\Modules\Emergencies\Controllers\Admin\EmergencyCreationController::class, 'emergencyForm'])->name('emergencies.create');
    Route::post('/emergencies', [\App\Modules\Emergencies\Controllers\Admin\EmergencyCreationController::class, 'storeEmergency'])->name('emergencies.store');
    Route::get('/emergencies', [\App\Modules\Emergencies\Controllers\Admin\EmergencyAdminController::class, 'index'])->name('emergencies.index');
    Route::put('/emergencies/{requestId}', [\App\Modules\Emergencies\Controllers\Admin\EmergencyAdminController::class, 'update'])->name('emergencies.update');
});
