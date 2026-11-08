<?php

namespace App\Modules\Catalog\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Requests\ApiSubServiceCatalogRequest;
use App\Modules\Catalog\Services\ApiSubServiceCatalogService;
use Illuminate\Http\JsonResponse;

/** متحكم API للخدمات الفرعية؛ يفوض القراءة إلى الخدمة. */
class SubServiceCatalogController extends Controller
{
    public function __invoke(ApiSubServiceCatalogRequest $request, ApiSubServiceCatalogService $service): JsonResponse
    {
        return response()->json($service($request->validated(), $request->user()));
    }
}
