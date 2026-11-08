<?php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\{EnsureAdmin,EnsureProvider,EnsureResident,SetApiLocale,SetLocale};
return Application::configure(basePath: dirname(__DIR__))
 ->withRouting(web:__DIR__.'/../routes/web.php',api:__DIR__.'/../routes/api.php',commands:__DIR__.'/../routes/console.php',health:'/up')
 ->withMiddleware(function(Middleware $middleware):void{
  $middleware->redirectTo(
      guests: fn (\Illuminate\Http\Request $request): string => $request->is('provider/*') ? '/provider/login' : '/admin/login',
      users: '/admin/dashboard',
  );
  $middleware->web(append:[SetLocale::class]);
  $middleware->api(append:[SetApiLocale::class]);
  $middleware->alias(['admin'=>EnsureAdmin::class,'provider'=>EnsureProvider::class,'resident'=>EnsureResident::class]);
 })
 ->withExceptions(function (Exceptions $exceptions): void {
  // الخدمات تصدر أخطاء مجال، وهذه الطبقة وحدها تختار كود HTTP.
  $exceptions->render(function (\App\Core\Exceptions\DomainViolation $error, \Illuminate\Http\Request $request) {
   $status = match ($error->reason) {
    \App\Core\Enums\FailureReason::NotFound => 404,
    \App\Core\Enums\FailureReason::Forbidden => 403,
    \App\Core\Enums\FailureReason::Unauthorized => 401,
    \App\Core\Enums\FailureReason::Conflict => 409,
    \App\Core\Enums\FailureReason::InvalidInput => 422,
    \App\Core\Enums\FailureReason::Configuration => 500,
   };
   return $request->expectsJson()
    ? response()->json(['message' => $error->getMessage()], $status)
    : response(e($error->getMessage()), $status);
  });
 })
 ->create();
