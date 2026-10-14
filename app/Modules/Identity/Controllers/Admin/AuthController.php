<?php
namespace App\Modules\Identity\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Modules\Identity\Requests\AdminLoginRequest;
use App\Modules\Identity\Requests\AdminSessionRequest;
use App\Modules\Identity\Services\AdminAuthService;
/** نقل HTTP الخاص بمصادقة لوحة الإدارة. */
class AuthController extends Controller
{
    /** @param AdminAuthService $service @return void */
    public function __construct(private readonly AdminAuthService $service) {}
    /** @return \Illuminate\Contracts\View\View */
    public function show(): \Illuminate\Contracts\View\View { return view('admin.login'); }
    /**
     * @param AdminLoginRequest $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(AdminLoginRequest $request): \Illuminate\Http\RedirectResponse
    {
        $this->service->login($request->validated());
        $request->session()->regenerate();
        return redirect()->intended(route('admin.dashboard'));
    }
    /**
     * @param AdminSessionRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(AdminSessionRequest $request): \Illuminate\Http\RedirectResponse
    {
        $this->service->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
