<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request;
/** طبقة مشتركة لتطبيق إعدادات وصلاحيات الطلبات عبر المجالات. */
class EnsureResident {
 /**
 * تطبيق سياسة مشتركة قبل استكمال دورة التطبيق.
 * @param Request $request
 * @param Closure $next
 * @return mixed
 * @throws \Throwable
 */
 public function handle(Request $request,Closure $next){
  abort_unless($request->user()?->role?->code===\App\Modules\Identity\Enums\RoleCode::Resident->value,403);
  return $next($request);
 }
}
