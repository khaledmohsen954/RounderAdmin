<?php

use App\Modules\Support\Controllers\Admin\SupportTicketAdminController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/support-tickets')->name('admin.support-tickets.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [SupportTicketAdminController::class, 'index'])->name('index');
    Route::get('/{id}', [SupportTicketAdminController::class, 'show'])->whereNumber('id')->name('show');
    Route::put('/{id}', [SupportTicketAdminController::class, 'update'])->whereNumber('id')->name('update');
});

