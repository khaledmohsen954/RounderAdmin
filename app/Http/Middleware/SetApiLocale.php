<?php
namespace App\\Http\\Middleware;
use Closure; use Illuminate\\Http\\Request; use Illuminate\\Support\\Facades\\App;
class SetApiLocale {
 public function handle(Request $request,Closure $next){
  $requested=strtolower(substr($request->header('Accept-Language','ar'),0,2));
  $locale=in_array($requested,['ar','en','ur'],true)?$requested:'ar';
  if($request->user()&&in_array($request->user()->locale,['ar','en','ur'],true)) $locale=$request->user()->locale;
  App::setLocale($locale); return $next($request);
 }
}
