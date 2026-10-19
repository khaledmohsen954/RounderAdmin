<?php
namespace App\Providers;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
/** طبقة مشتركة لتطبيق إعدادات وصلاحيات الطلبات عبر المجالات. */
class AppServiceProvider extends ServiceProvider {
 /**
 * تطبيق سياسة مشتركة قبل استكمال دورة التطبيق.
 * @return mixed
 * @throws \Throwable
 */
 public function register():void {}
 /**
 * تطبيق سياسة مشتركة قبل استكمال دورة التطبيق.
 * @return mixed
 * @throws \Throwable
 */
 public function boot():void { Paginator::useBootstrapFive();
 foreach ([\App\Modules\Emergencies\Models\EmergencyRequest::class,\App\Modules\Properties\Models\Home::class,\App\Modules\Bookings\Models\Booking::class,\App\Modules\Providers\Models\ProviderProfile::class] as $model) {
     $model::observe(\App\Modules\Notifications\Observers\AdminActivityObserver::class);
 }
 \Illuminate\Support\Facades\Blade::if('adminCan', fn (string $route, string $method = 'GET') => app(\App\Modules\Access\Services\AdminPermissionService::class)->allowsRoute(auth()->user(), $route, $method)); }
}
