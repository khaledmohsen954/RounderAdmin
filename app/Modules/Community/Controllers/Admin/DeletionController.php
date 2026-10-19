<?php
namespace App\Modules\Community\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Modules\Community\Requests\DeleteRecordRequest;
use App\Modules\Community\Services\AdminDeletionService;
use Illuminate\Http\RedirectResponse;
/** النقل HTTP فقط؛ صلاحية الحذف مستقلة عن التعديل. */
class DeletionController extends Controller
{
    /** @param AdminDeletionService $service */
    public function __construct(private readonly AdminDeletionService $service) {}
    /** @param DeleteRecordRequest $request @param int $id @return RedirectResponse @throws \Throwable */
    public function announcement(DeleteRecordRequest $request, int $id): RedirectResponse
    {
        $this->service->announcement($id,$request->user()->id);
        return back()->with('status',__('Deleted successfully.'));
    }
    /** @param DeleteRecordRequest $request @param int $id @return RedirectResponse @throws \Throwable */
    public function initiative(DeleteRecordRequest $request, int $id): RedirectResponse
    {
        $this->service->initiative($id,$request->user()->id);
        return back()->with('status',__('Deleted successfully.'));
    }
}
