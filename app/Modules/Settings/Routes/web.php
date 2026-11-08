<?php

use Illuminate\Support\Facades\Route;

// مسارات Settings فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.

Route::post('/locale', [\App\Modules\Settings\Controllers\Admin\LocaleController::class, 'update'])->name('locale.update');

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/pages', [\App\Modules\Settings\Controllers\Admin\PageAdminController::class, 'index'])->name('admin.pages.index');
    Route::get('/pages/{page}/edit', [\App\Modules\Settings\Controllers\Admin\PageAdminController::class, 'edit'])->whereNumber('page')->name('admin.pages.edit');
    Route::put('/pages/{page}', [\App\Modules\Settings\Controllers\Admin\PageAdminController::class, 'update'])->whereNumber('page')->name('admin.pages.update');

    Route::get('/settings/company', [\App\Modules\Settings\Controllers\Admin\CompanySettingsController::class, 'edit'])->name('admin.company-settings.edit');
    Route::put('/settings/company', [\App\Modules\Settings\Controllers\Admin\CompanySettingsController::class, 'update'])->name('admin.company-settings.update');
});
