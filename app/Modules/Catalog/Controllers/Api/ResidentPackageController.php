<?php

namespace App\Modules\Catalog\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Services\ApiResidentPackageService;
use App\Modules\Identity\Models\User;
use App\Modules\Catalog\Requests\ApiResidentPackageIndexRequest;
use App\Modules\Catalog\Requests\ApiResidentPackageSubscribeRequest;
use App\Modules\Catalog\Requests\ApiResidentPackageCancelRequest;

/** متحكم HTTP لمجال Catalog؛ يفوض جميع العمليات إلى الخدمة. */
class ResidentPackageController extends Controller
{
    /**
     * حقن خدمة المجال؛ لا توجد استعلامات أو قواعد عمل داخل المتحكم.
     * @param ApiResidentPackageService $service
     * @return void
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __construct(private readonly ApiResidentPackageService $service)
    {
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiResidentPackageIndexRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function index(ApiResidentPackageIndexRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->index($request->validated(), $request->user()), 200);
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiResidentPackageSubscribeRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function subscribe(ApiResidentPackageSubscribeRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->subscribe($request->validated(), $request->user()), 201);
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiResidentPackageCancelRequest $request
     * @param int $subscriptionId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function cancel(ApiResidentPackageCancelRequest $request, int $subscriptionId): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->cancel($request->validated(), $request->user(), $subscriptionId), 200);
    }
}
