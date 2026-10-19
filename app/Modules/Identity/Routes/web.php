<?php

use Illuminate\Support\Facades\Route;

// مسارات Identity فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.
Route::prefix('admin')->name('admin.')->middleware(['guest'])->group(function () {
    Route::get('/login', [\App\Modules\Identity\Controllers\Admin\AuthController::class, 'show'])->name('login');
    Route::post('/login', [\App\Modules\Identity\Controllers\Admin\AuthController::class, 'login'])->middleware('throttle:8,1')->name('login.submit');
});
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/users', [\App\Modules\Identity\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::post('/users', [\App\Modules\Identity\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::get('/users/create', [\App\Modules\Identity\Controllers\Admin\UserController::class, 'create'])->name('users.create');
    Route::get('/users/{user}/edit', [\App\Modules\Identity\Controllers\Admin\UserController::class, 'edit'])->whereNumber('user')->name('users.edit');
    Route::delete('/users/{user}', [\App\Modules\Identity\Controllers\Admin\UserController::class, 'destroy'])->whereNumber('user')->name('users.destroy');
    Route::put('/users/{user}', [\App\Modules\Identity\Controllers\Admin\UserController::class, 'update'])->name('users.update');
    Route::put('/users/{user}/password', [\App\Modules\Identity\Controllers\Admin\UserController::class, 'resetPassword'])->name('users.password');
    Route::post('/logout', [\App\Modules\Identity\Controllers\Admin\AuthController::class, 'logout'])->name('logout');
});
