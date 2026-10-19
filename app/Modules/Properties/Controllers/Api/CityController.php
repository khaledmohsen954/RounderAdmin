<?php
namespace App\Modules\Properties\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Modules\Properties\Requests\CityIndexRequest;
use App\Modules\Properties\Services\CityCatalogService;
use Illuminate\Http\JsonResponse;
/** نقل HTTP فقط؛ البحث والترجمة داخل خدمة المجال. */
class CityController extends Controller
{
    /** @param CityCatalogService $service */
    public function __construct(private readonly CityCatalogService $service) {}
    /** @param CityIndexRequest $request @return JsonResponse */
    public function __invoke(CityIndexRequest $request): JsonResponse
    {
        return response()->json($this->service->index($request->validated()));
    }
}
