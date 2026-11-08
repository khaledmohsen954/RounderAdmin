<?php

namespace App\Modules\Identity\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Services\AdminResidentService;
use App\Modules\Identity\Requests\AdminUserIndexRequest;
use Illuminate\Contracts\View\View;

class ResidentController extends Controller
{
    public function __construct(private readonly AdminResidentService $service)
    {
    }

    public function index(AdminUserIndexRequest $request): View
    {
        return view('admin.residents.index', $this->service->index($request->validated(), $request->user()));
    }
}
