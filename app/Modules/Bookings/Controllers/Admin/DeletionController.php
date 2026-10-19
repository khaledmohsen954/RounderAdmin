<?php
namespace App\Modules\Bookings\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Modules\Bookings\Requests\DeleteRecordRequest;
use App\Modules\Bookings\Services\AdminDeletionService;
use Illuminate\Http\RedirectResponse;
/** النقل HTTP فقط؛ صلاحية الحذف مستقلة عن التعديل. */
class DeletionController extends Controller
{
    /** @param AdminDeletionService $service */
    public function __construct(private readonly AdminDeletionService $service) {}
    /** @param DeleteRecordRequest $request @param int $id @return RedirectResponse @throws \Throwable */
    public function booking(DeleteRecordRequest $request, int $id): RedirectResponse
    {
        $this->service->booking($id,$request->user()->id);
        return back()->with('status',__('Deleted successfully.'));
    }
}
