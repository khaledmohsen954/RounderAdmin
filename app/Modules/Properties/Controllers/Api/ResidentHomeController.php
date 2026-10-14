<?php

namespace App\Modules\Properties\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Properties\Services\ApiResidentHomeService;
use App\Modules\Identity\Models\User;
use App\Modules\Properties\Requests\ApiResidentHomeIndexRequest;
use App\Modules\Properties\Requests\ApiResidentHomeLinkRequest;

/** متحكم HTTP لمجال Properties؛ يفوض جميع العمليات إلى الخدمة. */
class ResidentHomeController extends Controller
{
    /**
     * حقن خدمة المجال؛ لا توجد استعلامات أو قواعد عمل داخل المتحكم.
     * @param ApiResidentHomeService $service
     * @return void
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __construct(private readonly ApiResidentHomeService $service)
    {
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiResidentHomeIndexRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function index(ApiResidentHomeIndexRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->index($request->validated(), $request->user()), 200);
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiResidentHomeLinkRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function link(ApiResidentHomeLinkRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->link($request->validated(), $request->user()), 201);
    }
}
