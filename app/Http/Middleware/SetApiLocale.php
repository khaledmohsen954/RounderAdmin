<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request; use Illuminate\Support\Facades\App;
/** طبقة مشتركة لتطبيق إعدادات وصلاحيات الطلبات عبر المجالات. */
class SetApiLocale {
 /**
 * تطبيق سياسة مشتركة قبل استكمال دورة التطبيق.
 * @param Request $request
 * @param Closure $next
 * @return mixed
 * @throws \Throwable
 */
 public function handle(Request $request,Closure $next){
  $header = strtolower(substr($request->header('Accept-Language',''),0,2));
  if (in_array($header, ['ar','en','ur'], true)) {
      $locale = $header;
  } elseif ($request->user() && in_array($request->user()->locale, ['ar','en','ur'], true)) {
      $locale = $request->user()->locale;
  } else {
      $locale = 'ar';
  }
  App::setLocale($locale); return $next($request);
 }
}
