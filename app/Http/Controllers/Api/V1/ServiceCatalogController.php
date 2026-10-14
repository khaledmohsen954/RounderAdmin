<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
class ServiceCatalogController extends Controller {
 public function __invoke():JsonResponse {
  $locale=app()->getLocale();
  $services=Service::with(['category.translations','translations'])->where('is_active',true)->orderBy('service_category_id')->orderBy('id')->get()
   ->map(fn(Service $service)=>[
    'id'=>$service->id,'code'=>$service->code,'name'=>$service->translations->firstWhere('locale',$locale)?->name ?? $service->code,
    'description'=>$service->translations->firstWhere('locale',$locale)?->description,
    'price'=>(float)$service->base_price,
    'category'=>['id'=>$service->category->id,'code'=>$service->category->code,'name'=>$service->category->translations->firstWhere('locale',$locale)?->name ?? $service->category->code],
   ]);
  return response()->json(['data'=>$services]);
 }
}
