<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\{
    AboutController,
    AuthController,
    DashboardController,
    EmergencyAdminController,
    HomeController,
    LocaleController,
    PackageAdminController,
    ProviderController,
    ResidentContentController,
    ServiceOrderAdminController,
    UserController
};

Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');
Route::redirect('/login', '/admin/login')->name('login');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'show'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:8,1')->name('login.submit');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/', fn () => redirect()->route('admin.dashboard'));
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::get('/about', AboutController::class)->name('about');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::put('/users/{user}/password', [UserController::class, 'resetPassword'])->name('users.password');

        Route::get('/homes', [HomeController::class, 'index'])->name('homes.index');
        Route::post('/homes', [HomeController::class, 'store'])->name('homes.store');

        Route::get('/providers', [ProviderController::class, 'index'])->name('providers.index');
        Route::post('/providers', [ProviderController::class, 'store'])->name('providers.store');

        Route::get('/packages', [PackageAdminController::class, 'index'])->name('packages.index');
        Route::post('/packages/services', [PackageAdminController::class, 'storeService'])->name('packages.services.store');
        Route::post('/packages', [PackageAdminController::class, 'storePackage'])->name('packages.store');

        Route::get('/content', [ResidentContentController::class, 'index'])->name('content.index');
        Route::post('/content/announcements', [ResidentContentController::class, 'storeAnnouncement'])->name('content.announcements.store');
        Route::post('/content/initiatives', [ResidentContentController::class, 'storeInitiative'])->name('content.initiatives.store');
        Route::put('/content/facility-reports/{reportId}', [ResidentContentController::class, 'updateFacilityReport'])->name('content.reports.update');

        Route::get('/service-orders', [ServiceOrderAdminController::class, 'index'])->name('service-orders.index');
        Route::put('/service-orders/{bookingId}', [ServiceOrderAdminController::class, 'update'])->name('service-orders.update');
        Route::get('/emergencies', [EmergencyAdminController::class, 'index'])->name('emergencies.index');
        Route::put('/emergencies/{requestId}', [EmergencyAdminController::class, 'update'])->name('emergencies.update');

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});

Route::redirect('/', '/admin/login');
