<?php
namespace App\Modules\Access\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Modules\Access\Requests\{PermissionSetRequest, PermissionSetReadRequest};
use App\Modules\Access\Services\PermissionSetService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
/** واجهات HTTP لشاشة الصلاحيات. */
class PermissionSetController extends Controller
{
    /** @param PermissionSetService $service */
    public function __construct(private readonly PermissionSetService $service) {}
    /** @param PermissionSetReadRequest $request @return View */
    public function index(PermissionSetReadRequest $request): View { return view('admin.permissions.index',$this->service->index()); }
    /** @param PermissionSetReadRequest $request @return View */
    public function create(PermissionSetReadRequest $request): View { return view('admin.permissions.form',$this->service->form()); }
    /** @param PermissionSetReadRequest $request @param int $id @return View @throws \Throwable */
    public function edit(PermissionSetReadRequest $request, int $id): View { return view('admin.permissions.form',$this->service->form($id)); }
    /** @param PermissionSetRequest $request @return RedirectResponse @throws \Throwable */
    public function store(PermissionSetRequest $request): RedirectResponse
    {
        $this->service->save($request->validated(),$request->user());
        return redirect()->route('admin.permissions.index')->with('status',__('Saved successfully.'));
    }
    /** @param PermissionSetRequest $request @param int $id @return RedirectResponse @throws \Throwable */
    public function update(PermissionSetRequest $request, int $id): RedirectResponse
    {
        $this->service->save($request->validated(),$request->user(),$id);
        return redirect()->route('admin.permissions.index')->with('status',__('Saved successfully.'));
    }
    /** @param PermissionSetReadRequest $request @param int $id @return RedirectResponse @throws \Throwable */
    public function destroy(PermissionSetReadRequest $request, int $id): RedirectResponse
    {
        $this->service->delete($id,$request->user());
        return redirect()->route('admin.permissions.index')->with('status',__('Deleted successfully.'));
    }
}
