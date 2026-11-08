<?php

namespace App\Modules\Properties\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Properties\Services\ApiResidentHomeService;
use App\Modules\Identity\Models\User;
use App\Modules\Properties\Requests\ApiResidentHomeIndexRequest;
use App\Modules\Properties\Requests\ApiResidentHomeLinkRequest;
use App\Modules\Properties\Requests\ApiAddUnitRequest;
use App\Modules\Properties\Requests\ApiAddPropertyRequest;
use App\Modules\Properties\Requests\ApiSetPrimaryHomeRequest;
use App\Modules\Properties\Requests\ApiDeleteUnitRequest;

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

    /**
     * حفظ السكن وإضافة وحدة جديدة للساكن (تدفق الفيجما).
     * @param ApiAddUnitRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addUnit(ApiAddUnitRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->addUnit($request->validated(), $request->user()), 201);
    }

    /**
     * حفظ عقار جديد للمستخدم وربطه بحسابه تلقائياً.
     * @param ApiAddPropertyRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addProperty(ApiAddPropertyRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->addProperty($request->validated(), $request->user()), 201);
    }

    /**
     * تعيين السكن أو الوحدة كوحدة أساسية نشطة للساكن.
     * @param ApiSetPrimaryHomeRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setPrimary(ApiSetPrimaryHomeRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->setPrimary($request->validated(), $request->user()), 200);
    }

    /**
     * حذف السكن أو إلغاء ربط الوحدة بحساب الساكن.
     * @param ApiDeleteUnitRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteUnit(ApiDeleteUnitRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->deleteUnit($request->validated(), $request->user()), 200);
    }
}
