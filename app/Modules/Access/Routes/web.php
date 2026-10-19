<?php
use Illuminate\Support\Facades\Route;
use App\Modules\Access\Controllers\Admin\PermissionSetController;
Route::prefix('admin/permissions')->name('admin.permissions.')->middleware(['auth','admin'])->group(function () {
    Route::get('/',[PermissionSetController::class,'index'])->name('index');
    Route::get('/create',[PermissionSetController::class,'create'])->name('create');
    Route::post('/',[PermissionSetController::class,'store'])->name('store');
    Route::get('/{id}/edit',[PermissionSetController::class,'edit'])->whereNumber('id')->name('edit');
    Route::put('/{id}',[PermissionSetController::class,'update'])->whereNumber('id')->name('update');
    Route::delete('/{id}',[PermissionSetController::class,'destroy'])->whereNumber('id')->name('destroy');
});
