<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request; use Illuminate\Support\Facades\App;
/** طبقة مشتركة لتطبيق إعدادات وصلاحيات الطلبات عبر المجالات. */
class SetLocale {
 /**
 * تطبيق سياسة مشتركة قبل استكمال دورة التطبيق.
 * @param Request $request
 * @param Closure $next
 * @return mixed
 * @throws \Throwable
 */
 public function handle(Request $request, Closure $next) {
  $locale=$request->user()?->locale ?? $request->session()->get('locale',config('app.locale','ar'));
  App::setLocale(in_array($locale,['ar','en'],true)?$locale:'ar');
  return $next($request);
 }
}
