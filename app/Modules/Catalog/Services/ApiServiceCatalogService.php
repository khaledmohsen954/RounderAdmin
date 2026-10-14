<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Models\Service;
use App\Modules\Identity\Models\User;

/** قواعد عمل Catalog؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class ApiServiceCatalogService
{

    /**
     * قراءة بيانات المجال مع الحفاظ على قيود الوصول.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __invoke(array $input, ?User $actor): mixed
    {
        $locale=app()->getLocale();
  $services=Service::with(['category.translations','translations'])->where('is_active',true)->orderBy('service_category_id')->orderBy('id')->get()
   ->map(fn(Service $service)=>[
    'id'=>$service->id,'code'=>$service->code,'name'=>$service->translations->firstWhere('locale',$locale)?->name ?? $service->code,
    'description'=>$service->translations->firstWhere('locale',$locale)?->description,
    'price'=>(float)$service->base_price,
    'category'=>['id'=>$service->category->id,'code'=>$service->category->code,'name'=>$service->category->translations->firstWhere('locale',$locale)?->name ?? $service->category->code],
   ]);
  return ['data'=>$services];
    }
}
