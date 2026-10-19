<?php
use Illuminate\Support\Facades\Route;
use App\Modules\Notifications\Controllers\AdminAlertController;
Route::prefix('admin/notifications')->name('admin.notifications.')->middleware(['auth','admin'])->group(function(){
    Route::get('/',[AdminAlertController::class,'index'])->name('index');
    Route::post('/read-all',[AdminAlertController::class,'readAll'])->name('read-all');
    Route::post('/{id}/read',[AdminAlertController::class,'read'])->whereNumber('id')->name('read');
});
