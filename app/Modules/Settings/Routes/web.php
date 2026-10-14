<?php

use Illuminate\Support\Facades\Route;

// مسارات Settings فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.

Route::post('/locale', [\App\Modules\Settings\Controllers\Admin\LocaleController::class, 'update'])->name('locale.update');
