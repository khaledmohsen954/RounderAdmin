<?php

namespace App\Modules\Catalog\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Services\ApiServiceCategoryService;
use App\Modules\Identity\Models\User;
use App\Modules\Catalog\Requests\ApiServiceCategory__invokeRequest;

/** متحكم HTTP لمجال Catalog؛ يفوض جميع العمليات إلى الخدمة. */
class ServiceCategoryController extends Controller
{
    /**
     * حقن خدمة المجال؛ لا توجد استعلامات أو قواعد عمل داخل المتحكم.
     * @param ApiServiceCategoryService $service
     * @return void
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __construct(private readonly ApiServiceCategoryService $service)
    {
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiServiceCategory__invokeRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __invoke(ApiServiceCategory__invokeRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->__invoke($request->validated(), $request->user()), 200);
    }
}
