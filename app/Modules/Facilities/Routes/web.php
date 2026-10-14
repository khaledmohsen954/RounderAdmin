<?php

use Illuminate\Support\Facades\Route;

// مسارات Facilities فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::put('/content/facility-reports/{reportId}', [\App\Modules\Facilities\Controllers\Admin\FacilityAdminController::class, 'updateFacilityReport'])->name('content.reports.update');
});
