<?php

namespace App\Modules\Support\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Support\Requests\AdminSupportTicketIndexRequest;
use App\Modules\Support\Requests\AdminUpdateSupportTicketRequest;
use App\Modules\Support\Services\AdminSupportTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SupportTicketAdminController extends Controller
{
    public function __construct(
        private readonly AdminSupportTicketService $adminService
    ) {
    }

    public function index(AdminSupportTicketIndexRequest $request): View
    {
        $filters = $request->validated();
        $tickets = $this->adminService->list($filters);
        $counts = $this->adminService->counts();

        return view('admin.support-tickets.index', compact('tickets', 'counts', 'filters'));
    }

    public function show(int $id): View
    {
        $ticket = $this->adminService->find($id);

        return view('admin.support-tickets.show', compact('ticket'));
    }

    public function update(AdminUpdateSupportTicketRequest $request, int $id): RedirectResponse
    {
        $this->adminService->update($id, $request->validated(), $request->user());

        return redirect()->route('admin.support-tickets.show', $id)
            ->with('status', 'تم تحديث حالة التذكرة وحفظ الرد بنجاح.');
    }
}

