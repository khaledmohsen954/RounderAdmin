<?php
namespace App\Modules\Community\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Modules\Community\Services\AdminContentEditingService;
use App\Modules\Community\Requests\{DeleteRecordRequest,UpdateAnnouncementRequest,UpdateInitiativeRequest};
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
/** صفحات التعديل تستعمل خدمات المجال وترجع الاستجابة فقط. */
class ContentEditingController extends Controller
{
    /** @param AdminContentEditingService $service */
    public function __construct(private readonly AdminContentEditingService $service) {}
    /** @param DeleteRecordRequest $request @param int $id @return View */
    public function announcement(DeleteRecordRequest $request,int $id): View { return view('admin.content.edit',$this->service->announcement($id)); }
    /** @param DeleteRecordRequest $request @param int $id @return View */
    public function initiative(DeleteRecordRequest $request,int $id): View { return view('admin.content.edit',$this->service->initiative($id)); }
    /** @param UpdateAnnouncementRequest $request @param int $id @return RedirectResponse @throws \Throwable */
    public function updateAnnouncement(UpdateAnnouncementRequest $request,int $id): RedirectResponse
    {
        $this->service->updateAnnouncement($id,$request->validated(),$request->user()->id);
        return redirect()->route('admin.content.index')->with('status',__('Saved successfully.'));
    }
    /** @param UpdateInitiativeRequest $request @param int $id @return RedirectResponse @throws \Throwable */
    public function updateInitiative(UpdateInitiativeRequest $request,int $id): RedirectResponse
    {
        $this->service->updateInitiative($id,$request->validated(),$request->user()->id);
        return redirect()->route('admin.content.index')->with('status',__('Saved successfully.'));
    }
}
