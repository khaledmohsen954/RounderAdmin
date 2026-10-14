<?php

namespace App\Modules\Notifications\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Notifications\Services\ApiNotificationService;
use App\Modules\Identity\Models\User;
use App\Modules\Notifications\Requests\ApiNotificationNotificationsRequest;
use App\Modules\Notifications\Requests\ApiNotificationMarkNotificationReadRequest;

/** متحكم HTTP لمجال Notifications؛ يفوض جميع العمليات إلى الخدمة. */
class NotificationController extends Controller
{
    /**
     * حقن خدمة المجال؛ لا توجد استعلامات أو قواعد عمل داخل المتحكم.
     * @param ApiNotificationService $service
     * @return void
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __construct(private readonly ApiNotificationService $service)
    {
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiNotificationNotificationsRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function notifications(ApiNotificationNotificationsRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->notifications($request->validated(), $request->user()), 200);
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiNotificationMarkNotificationReadRequest $request
     * @param int $notificationId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function markNotificationRead(ApiNotificationMarkNotificationReadRequest $request, int $notificationId): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->markNotificationRead($request->validated(), $request->user(), $notificationId), 200);
    }
}
