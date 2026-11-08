<?php

namespace App\Modules\Emergencies\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Emergencies\Requests\ApiEmergencyTypeIndexRequest;
use App\Modules\Emergencies\Services\ApiEmergencyTypeService;
use Illuminate\Http\JsonResponse;

/** HTTP controller for the resident emergency type catalog. */
class EmergencyTypeController extends Controller
{
    public function __construct(private readonly ApiEmergencyTypeService $service)
    {
    }

    public function index(ApiEmergencyTypeIndexRequest $request): JsonResponse
    {
        return response()->json($this->service->index($request->validated(), $request->user()), 200);
    }
}
