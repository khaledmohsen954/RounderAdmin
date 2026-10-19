<?php
namespace App\Modules\Notifications\Controllers;
use App\Modules\Notifications\Services\AdminAlertService;
use App\Modules\Notifications\Requests\AlertRequest;
use Illuminate\Http\JsonResponse;
/** HTTP transport only; event queries and authorization remain in the service. */
class AdminAlertController {
    /** @param AdminAlertService $service */
    public function __construct(private AdminAlertService $service) {}
    /** @param AlertRequest $request @return JsonResponse */
    public function index(AlertRequest $request): JsonResponse {
        return response()->json($this->service->feed($request->user()))->header('Cache-Control','private, no-store');
    }
    /** @param AlertRequest $request @param int $id @return JsonResponse @throws \Throwable */
    public function read(AlertRequest $request,int $id): JsonResponse {
        $this->service->markRead($request->user(),$id);
        return response()->json(['message'=>__('Notification marked as read')]);
    }
    /** @param AlertRequest $request @return JsonResponse @throws \Throwable */
    public function readAll(AlertRequest $request): JsonResponse {
        $this->service->markAllRead($request->user());
        return response()->json(['message'=>__('Notifications marked as read')]);
    }
}
