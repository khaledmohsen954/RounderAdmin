<?php

namespace App\Http\Controllers\Admin;

use App\Models\ProviderCommissionEntry;
use App\Models\ProviderContract;
use App\Models\ProviderContractStatus;
use App\Models\ProviderProfile;
use App\Models\Role;
use App\Models\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** إنشاء حساب المزود وملفه وعقد العمولة في معاملة واحدة. */
class ProviderController extends Controller
{
    public function index()
    {
        $providers = ProviderProfile::query()
            ->join('users', 'provider_profiles.user_id', '=', 'users.id')
            ->leftJoin('provider_contracts', function ($join) {
                $join->on('provider_profiles.id', '=', 'provider_contracts.provider_profile_id')
                    ->whereNull('provider_contracts.ends_on');
            })
            ->leftJoin('provider_contract_statuses', 'provider_contracts.status_id', '=', 'provider_contract_statuses.id')
            ->select(
                'provider_profiles.id as provider_id', 'provider_profiles.company_name',
                'provider_profiles.is_approved', 'users.id as user_id', 'users.name',
                'users.email', 'users.phone', 'provider_contracts.reference as contract_reference',
                'provider_contracts.service_commission_percent', 'provider_contracts.emergency_commission_percent',
                'provider_contracts.starts_on', 'provider_contract_statuses.code as contract_status'
            )->selectSub(ProviderCommissionEntry::query()->selectRaw('COALESCE(SUM(commission_amount), 0)')
                ->whereColumn('provider_commission_entries.provider_profile_id', 'provider_profiles.id'), 'commission_total')
            ->orderByDesc('provider_profiles.id')->paginate(20);

        return view('admin.providers.index', compact('providers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:254', 'unique:users,email'],
            'phone' => ['required', 'regex:/^\+[1-9][0-9]{7,14}$/', 'unique:users,phone'],
            'locale' => ['required', Rule::in(['ar', 'en', 'ur'])],
            'password' => ['required', 'string', 'min:12', 'max:72', 'confirmed', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/'],
            'company_name' => ['required', 'string', 'min:2', 'max:160'],
            'is_approved' => ['required', 'boolean'],
            'service_commission_percent' => ['required', 'numeric', 'between:0,100'],
            'emergency_commission_percent' => ['required', 'numeric', 'between:0,100'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'terms' => ['nullable', 'string', 'max:10000'],
        ]);

        DB::transaction(function () use ($request, $data) {
            $roleId = Role::query()->where('code', 'provider')->value('id');
            abort_unless($roleId, 500, 'Provider role is not configured.');

            $userId = User::query()->create([
                'role_id' => $roleId,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'locale' => $data['locale'],
                'password' => bcrypt($data['password']),
                'created_at' => now(),
                'updated_at' => now(),
            ])->getKey();
            $providerId = ProviderProfile::query()->create([
                'user_id' => $userId,
                'company_name' => $data['company_name'],
                'is_approved' => $data['is_approved'],
                'created_at' => now(),
                'updated_at' => now(),
            ])->getKey();
            $contractStatus = ProviderContractStatus::query()
                ->where('code', $data['is_approved'] ? 'active' : 'draft')->value('id');
            abort_unless($contractStatus, 500, 'Provider contract statuses are not configured.');

            ProviderContract::query()->create([
                'provider_profile_id' => $providerId,
                'status_id' => $contractStatus,
                'reference' => 'PC-' . Str::upper(Str::random(12)),
                'service_commission_percent' => $data['service_commission_percent'],
                'emergency_commission_percent' => $data['emergency_commission_percent'],
                'starts_on' => $data['starts_on'],
                'ends_on' => $data['ends_on'] ?? null,
                'terms' => $data['terms'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return back()->with('status', __('Provider and contract created successfully.'));
    }
}
