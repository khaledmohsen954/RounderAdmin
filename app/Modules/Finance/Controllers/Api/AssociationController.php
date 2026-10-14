<?php

namespace App\Modules\Finance\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Services\ApiAssociationService;
use App\Modules\Identity\Models\User;
use App\Modules\Finance\Requests\ApiAssociationAssociationRequest;

/** متحكم HTTP لمجال Finance؛ يفوض جميع العمليات إلى الخدمة. */
class AssociationController extends Controller
{
    /**
     * حقن خدمة المجال؛ لا توجد استعلامات أو قواعد عمل داخل المتحكم.
     * @param ApiAssociationService $service
     * @return void
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __construct(private readonly ApiAssociationService $service)
    {
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiAssociationAssociationRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function association(ApiAssociationAssociationRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->association($request->validated(), $request->user()), 200);
    }
}
