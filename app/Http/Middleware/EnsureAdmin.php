<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use App\Modules\Access\Services\AdminPermissionService;
/** حماية الخادم لكل قسم وفعل، حتى عند إرسال طلب مباشر خارج الواجهة. */
class EnsureAdmin
{
    /** @param Request $request @param Closure $next @return mixed */
    public function handle(Request $request, Closure $next): mixed
    {
        abort_unless(app(AdminPermissionService::class)->allowsRoute(
            $request->user(), (string) $request->route()?->getName(), $request->method()), 403);
        return $next($request);
    }
}
