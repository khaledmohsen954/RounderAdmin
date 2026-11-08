<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LandingController;

// نقطة تسجيل موحدة؛ تعريفات كل مجال تبقى داخل Module.
foreach (glob(app_path('Modules/*/Routes/web.php')) as $moduleRoutes) {
    require $moduleRoutes;
}
Route::redirect('/login', '/admin/login')->name('login');
Route::redirect('/admin', '/admin/dashboard')->middleware(['auth']);
Route::get('/', [LandingController::class, 'index'])->name('home');
Route::post('/booking', [LandingController::class, 'storeBooking'])->name('landing.booking.store');
