<?php
namespace App\Modules\Catalog\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Modules\Catalog\Services\PackageFeatureService;
use App\Modules\Catalog\Requests\SavePackageFeatureRequest;
use Illuminate\Http\RedirectResponse;
/** نقل HTTP فقط؛ تعريفات المقارنة تديرها خدمة المجال. */
class PackageFeatureController extends Controller
{
    /** @param PackageFeatureService $service */
    public function __construct(private readonly PackageFeatureService $service) {}
    /**
     * @param SavePackageFeatureRequest $request
     * @return RedirectResponse
     * @throws \Throwable
     */
    public function store(SavePackageFeatureRequest $request): RedirectResponse
    {
        return back()->with('status',$this->service->save($request->validated(),null,$request->user()->id));
    }
    /**
     * @param SavePackageFeatureRequest $request
     * @param int $featureId
     * @return RedirectResponse
     * @throws \Throwable
     */
    public function update(SavePackageFeatureRequest $request,int $featureId): RedirectResponse
    {
        return back()->with('status',$this->service->save($request->validated(),$featureId,$request->user()->id));
    }
}
