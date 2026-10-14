<?php
use Illuminate\Support\Facades\Route;

// نقطة تسجيل موحدة؛ تعريفات كل مجال تبقى داخل Module.
foreach (glob(app_path('Modules/*/Routes/web.php')) as $moduleRoutes) {
    require $moduleRoutes;
}
Route::redirect('/login', '/admin/login')->name('login');
Route::redirect('/admin', '/admin/dashboard')->middleware(['auth', 'admin']);
Route::redirect('/', '/admin/login');
