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
 public function boot():void { Paginator::useBootstrapFive(); }
}
