<?php

use Illuminate\Support\Facades\Route;

Route::prefix('provider')->name('provider.')->group(function () {
    Route::get('/register', [\App\Modules\Providers\Controllers\Portal\ProviderRegistrationController::class, 'show'])->name('register');
    Route::post('/register', [\App\Modules\Providers\Controllers\Portal\ProviderRegistrationController::class, 'store'])->middleware('throttle:5,1')->name('register.submit');
    Route::get('/login', [\App\Modules\Identity\Controllers\Provider\AuthController::class, 'show'])->name('login');
    Route::post('/login', [\App\Modules\Identity\Controllers\Provider\AuthController::class, 'login'])->middleware('throttle:8,1')->name('login.submit');
});

Route::prefix('provider')->name('provider.')->middleware('auth:provider')->group(function () {
    Route::post('/logout', [\App\Modules\Identity\Controllers\Provider\AuthController::class, 'logout'])->name('logout');
    Route::get('/pending', [\App\Modules\Providers\Controllers\Portal\ProviderPortalController::class, 'pending'])->name('pending');
    Route::get('/contract', [\App\Modules\Providers\Controllers\Portal\ProviderPortalController::class, 'contract'])
        ->name('contract');
    Route::post('/contract/accept', [\App\Modules\Providers\Controllers\Portal\ProviderPortalController::class, 'acceptContract'])->name('contract.accept');
    Route::get('/contract/pdf', [\App\Modules\Providers\Controllers\Portal\ProviderPortalController::class, 'downloadContract'])
        ->name('contract.pdf');

    Route::get('/dashboard', [\App\Modules\Providers\Controllers\Portal\ProviderPortalController::class, 'dashboard'])
        ->middleware('provider:dashboard.view')->name('dashboard');
    Route::get('/orders', [\App\Modules\Providers\Controllers\Portal\ProviderPortalController::class, 'orders'])
        ->middleware('provider:orders.view')->name('orders.index');
    Route::get('/orders/create', [\App\Modules\Providers\Controllers\Portal\ProviderPortalController::class, 'createOrder'])
        ->middleware('provider:orders.create')->name('orders.create');
    Route::post('/orders', [\App\Modules\Providers\Controllers\Portal\ProviderPortalController::class, 'storeOrder'])
        ->middleware('provider:orders.create')->name('orders.store');
    Route::get('/orders/{bookingId}', [\App\Modules\Providers\Controllers\Portal\ProviderPortalController::class, 'showOrder'])
        ->whereNumber('bookingId')->middleware('provider:orders.view')->name('orders.show');
    Route::put('/orders/{bookingId}', [\App\Modules\Providers\Controllers\Portal\ProviderPortalController::class, 'updateOrder'])
        ->whereNumber('bookingId')->middleware('provider:orders.manage')->name('orders.update');
    Route::get('/prices', [\App\Modules\Providers\Controllers\Portal\ProviderPortalController::class, 'prices'])
        ->middleware('provider:prices.view')->name('prices.index');
    Route::get('/prices/create', [\App\Modules\Providers\Controllers\Portal\ProviderPortalController::class, 'createPrice'])
        ->middleware('provider:prices.manage')->name('prices.create');
    Route::post('/prices', [\App\Modules\Providers\Controllers\Portal\ProviderPortalController::class, 'storePrice'])
        ->middleware('provider:prices.manage')->name('prices.store');
    Route::get('/prices/{priceId}/edit', [\App\Modules\Providers\Controllers\Portal\ProviderPortalController::class, 'editPrice'])
        ->whereNumber('priceId')->middleware('provider:prices.manage')->name('prices.edit');
    Route::put('/prices/{priceId}', [\App\Modules\Providers\Controllers\Portal\ProviderPortalController::class, 'updatePrice'])
        ->whereNumber('priceId')->middleware('provider:prices.manage')->name('prices.update');
    Route::get('/emergencies', [\App\Modules\Providers\Controllers\Portal\ProviderPortalController::class, 'emergencies'])
        ->middleware('provider:emergencies.view')->name('emergencies.index');
    Route::get('/finance', [\App\Modules\Providers\Controllers\Portal\ProviderPortalController::class, 'financialAccount'])
        ->middleware('provider:finance.view')->name('finance.index');
    Route::get('/ratings', [\App\Modules\Providers\Controllers\Portal\ProviderPortalController::class, 'ratings'])
        ->middleware('provider:ratings.view')->name('ratings.index');
    Route::get('/settings', [\App\Modules\Providers\Controllers\Portal\ProviderPortalController::class, 'settings'])
        ->middleware('provider:profile.manage')->name('settings.index');
    Route::put('/settings', [\App\Modules\Providers\Controllers\Portal\ProviderPortalController::class, 'updateSettings'])
        ->middleware('provider:profile.manage')->name('settings.update');
    Route::post('/settings/services', [\App\Modules\Providers\Controllers\Portal\ProviderPortalController::class, 'addService'])
        ->middleware('provider:profile.manage')->name('settings.services.store');
    Route::delete('/settings/services/{serviceId}', [\App\Modules\Providers\Controllers\Portal\ProviderPortalController::class, 'removeService'])
        ->whereNumber('serviceId')->middleware('provider:profile.manage')->name('settings.services.destroy');
    Route::get('/team', [\App\Modules\Providers\Controllers\Portal\ProviderPortalController::class, 'team'])
        ->middleware('provider:users.manage')->name('team.index');
    Route::get('/team/create', [\App\Modules\Providers\Controllers\Portal\ProviderPortalController::class, 'createTeamMember'])
        ->middleware('provider:users.manage')->name('team.create');
    Route::post('/team', [\App\Modules\Providers\Controllers\Portal\ProviderPortalController::class, 'storeTeamMember'])
        ->middleware('provider:users.manage')->name('team.store');
    Route::get('/team/{memberId}/edit', [\App\Modules\Providers\Controllers\Portal\ProviderPortalController::class, 'editTeamMember'])
        ->whereNumber('memberId')->middleware('provider:users.manage')->name('team.edit');
    Route::put('/team/{memberId}', [\App\Modules\Providers\Controllers\Portal\ProviderPortalController::class, 'updateTeamMember'])
        ->whereNumber('memberId')->middleware('provider:users.manage')->name('team.update');
    Route::delete('/team/{memberId}', [\App\Modules\Providers\Controllers\Portal\ProviderPortalController::class, 'destroyTeamMember'])
        ->whereNumber('memberId')->middleware('provider:users.manage')->name('team.destroy');
});

// مسارات Providers فقط؛ الأسماء والروابط العامة محفوظة للتوافق مع العملاء.
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/providers', [\App\Modules\Providers\Controllers\Admin\ProviderController::class, 'index'])->name('providers.index');
    Route::post('/providers', [\App\Modules\Providers\Controllers\Admin\ProviderController::class, 'store'])->name('providers.store');
    Route::get('/providers/create', [\App\Modules\Providers\Controllers\Admin\ProviderController::class, 'create'])->name('providers.create');
    Route::get('/providers/{provider}/edit', [\App\Modules\Providers\Controllers\Admin\ProviderController::class, 'edit'])->whereNumber('provider')->name('providers.edit');
    Route::put('/providers/{provider}', [\App\Modules\Providers\Controllers\Admin\ProviderController::class, 'update'])->whereNumber('provider')->name('providers.update');
    Route::get('/providers/{provider}/documents/{document}', [\App\Modules\Providers\Controllers\Admin\ProviderDocumentController::class, 'show'])
        ->whereNumber('provider')->where('document', '[a-z_]+')->name('providers.documents.show');
    Route::get('/providers/{provider}/contract-pdf', [\App\Modules\Providers\Controllers\Admin\ProviderContractPdfController::class, 'download'])
        ->whereNumber('provider')->name('providers.contract.pdf');
});

Route::prefix('admin')->name('admin.')->middleware(['auth','admin'])->group(function () {
    Route::delete('/providers/{id}', [\App\Modules\Providers\Controllers\Admin\DeletionController::class, 'provider'])->whereNumber('id')->name('providers.destroy');
});
