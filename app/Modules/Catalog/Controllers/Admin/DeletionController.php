<?php
namespace App\Modules\Catalog\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Modules\Catalog\Requests\DeleteRecordRequest;
use App\Modules\Catalog\Services\AdminDeletionService;
use Illuminate\Http\RedirectResponse;
/** النقل HTTP فقط؛ صلاحية الحذف مستقلة عن التعديل. */
class DeletionController extends Controller
{
    /** @param AdminDeletionService $service */
    public function __construct(private readonly AdminDeletionService $service) {}
    /** @param DeleteRecordRequest $request @param int $id @return RedirectResponse @throws \Throwable */
    public function service(DeleteRecordRequest $request, int $id): RedirectResponse
    {
        $this->service->service($id,$request->user()->id);
        return back()->with('status',__('Deleted successfully.'));
    }
    /** @param DeleteRecordRequest $request @param int $id @return RedirectResponse @throws \Throwable */
    public function package(DeleteRecordRequest $request, int $id): RedirectResponse
    {
        $this->service->package($id,$request->user()->id);
        return back()->with('status',__('Deleted successfully.'));
    }
    /** @param DeleteRecordRequest $request @param int $id @return RedirectResponse @throws \Throwable */
    public function feature(DeleteRecordRequest $request, int $id): RedirectResponse
    {
        $this->service->feature($id,$request->user()->id);
        return back()->with('status',__('Deleted successfully.'));
    }
}
