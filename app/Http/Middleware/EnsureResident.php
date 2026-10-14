<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request;
class EnsureResident {
 public function handle(Request $request,Closure $next){
  abort_unless($request->user()?->role?->code==='resident',403);
  return $next($request);
 }
}
