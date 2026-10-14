<?php

namespace App\Http\Controllers\Admin;

use App\Models\EmergencyProviderAssignment;
use App\Models\EmergencyRequest;
use App\Models\EmergencyRequestStatus;
use App\Models\EmergencyRequestUpdate;
use App\Models\ProviderCommissionEntry;
use App\Models\ProviderCommissionStatus;
use App\Models\ProviderContract;
use App\Models\ProviderProfile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** إسناد ومتابعة الطوارئ وعقود المزود واحتساب عمولة الطوارئ. */
class EmergencyAdminController extends Controller
{
    public function index()
    {
        $locale = app()->getLocale();
        $requests = EmergencyRequest::query()
            ->join('users', 'emergency_requests.resident_id', '=', 'users.id')
            ->join('homes', 'emergency_requests.home_id', '=', 'homes.id')
            ->join('emergency_types', 'emergency_requests.emergency_type_id', '=', 'emergency_types.id')
            ->join('emergency_type_translations', function ($join) use ($locale) {
                $join->on('emergency_types.id', '=', 'emergency_type_translations.emergency_type_id')
                    ->where('emergency_type_translations.locale', '=', $locale);
            })
            ->join('emergency_request_statuses', 'emergency_requests.status_id', '=', 'emergency_request_statuses.id')
            ->join('emergency_request_status_translations', function ($join) use ($locale) {
                $join->on('emergency_request_statuses.id', '=', 'emergency_request_status_translations.emergency_request_status_id')
                    ->where('emergency_request_status_translations.locale', '=', $locale);
            })
            ->leftJoin('emergency_provider_assignments', 'emergency_requests.id', '=', 'emergency_provider_assignments.emergency_request_id')
            ->leftJoin('provider_profiles', 'emergency_provider_assignments.provider_profile_id', '=', 'provider_profiles.id')
            ->select(
                'emergency_requests.*', 'users.name as resident_name', 'users.phone as resident_phone',
                'homes.name as home_name', 'emergency_type_translations.name as type_name',
                'emergency_request_status_translations.name as status_name', 'emergency_request_statuses.code as status_code',
                'provider_profiles.company_name as provider_name',
                'emergency_provider_assignments.provider_contract_id'
            )->orderByDesc('emergency_requests.requested_at')->paginate(25);

        $providers = ProviderProfile::query()->join('users', 'provider_profiles.user_id', '=', 'users.id')
            ->where('provider_profiles.is_approved', true)->select('provider_profiles.id', 'provider_profiles.company_name', 'users.name')
            ->orderBy('provider_profiles.company_name')->get();
        $statuses = EmergencyRequestStatus::query()->join('emergency_request_status_translations', function ($join) use ($locale) {
            $join->on('emergency_request_statuses.id', '=', 'emergency_request_status_translations.emergency_request_status_id')
                ->where('emergency_request_status_translations.locale', '=', $locale);
        })->select('emergency_request_statuses.id', 'emergency_request_statuses.code', 'emergency_request_status_translations.name')->get();

        return view('admin.emergencies.index', compact('requests', 'providers', 'statuses'));
    }

    public function update(Request $request, int $requestId)
    {
        $emergency = EmergencyRequest::query()->where('id', $requestId)->first();
        abort_unless($emergency, 404);

        $data = $request->validate([
            'provider_profile_id' => ['nullable', 'integer', 'exists:provider_profiles,id'],
            'status_id' => ['required', 'integer', 'exists:emergency_request_statuses,id'],
            'gross_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);
        $status = EmergencyRequestStatus::query()->where('id', $data['status_id'])->first();
        $assignment = EmergencyProviderAssignment::query()->where('emergency_request_id', $emergency->id)->first();
        $providerId = $data['provider_profile_id'] ?? $assignment?->provider_profile_id;
        $contract = null;

        if ($providerId) {
            $provider = ProviderProfile::query()->where('id', $providerId)->where('is_approved', true)->first();
            abort_unless($provider, 422, __('Choose an approved provider.'));
            if ($assignment && (int) $assignment->provider_profile_id === (int) $providerId) {
                $contract = ProviderContract::query()->where('id', $assignment->provider_contract_id)->first();
            } else {
                $contract = ProviderContract::query()
                    ->join('provider_contract_statuses', 'provider_contracts.status_id', '=', 'provider_contract_statuses.id')
                    ->where('provider_contracts.provider_profile_id', $providerId)
                    ->where('provider_contract_statuses.code', 'active')
                    ->whereDate('provider_contracts.starts_on', '<=', today())
                    ->where(function ($query) {
                        $query->whereNull('provider_contracts.ends_on')->orWhereDate('provider_contracts.ends_on', '>=', today());
                    })->orderByDesc('provider_contracts.starts_on')->select('provider_contracts.*')->first();
            }
            abort_unless($contract, 422, __('Provider needs an active contract before assignment.'));
        }

        if ($status->code === 'assigned' && !$providerId) {
            throw ValidationException::withMessages(['provider_profile_id' => __('Select a provider before assignment.')]);
        }
        if ($status->code === 'resolved' && (!$providerId || ($data['gross_amount'] ?? null) === null)) {
            throw ValidationException::withMessages(['gross_amount' => __('Assign a provider and enter the final amount before resolving.')]);
        }

        DB::transaction(function () use ($request, $requestId, $emergency, $data, $status, $providerId, $contract, $assignment) {
            if ($providerId && $contract) {
                EmergencyProviderAssignment::query()->updateOrCreate(
                    ['emergency_request_id' => $requestId],
                    [
                        'provider_profile_id' => $providerId,
                        'provider_contract_id' => $contract->id,
                        'assigned_by' => $request->user()->id,
                        'assigned_at' => $assignment?->assigned_at ?? now(),
                        'completed_at' => $status->code === 'resolved' ? now() : null,
                        'provider_note' => $data['note'] ?? null,
                        'updated_at' => now(),
                        'created_at' => $assignment?->created_at ?? now(),
                    ]
                );
            }
            EmergencyRequest::query()->where('id', $requestId)->update([
                'status_id' => $status->id,
                'handled_by' => $request->user()->id,
                'dispatched_at' => in_array($status->code, ['assigned', 'on_the_way', 'resolved'], true) ? ($emergency->dispatched_at ?? now()) : null,
                'resolved_at' => $status->code === 'resolved' ? now() : null,
                'updated_at' => now(),
            ]);
            EmergencyRequestUpdate::query()->create([
                'emergency_request_id' => $requestId,
                'status_id' => $status->id,
                'updated_by' => $request->user()->id,
                'note' => $data['note'] ?? null,
                'created_at' => now(),
            ]);

            if ($status->code === 'resolved' && $providerId && $contract) {
                $pendingId = ProviderCommissionStatus::query()->where('code', 'pending')->value('id');
                $gross = (float) $data['gross_amount'];
                $rate = (float) $contract->emergency_commission_percent;
                ProviderCommissionEntry::query()->updateOrCreate(
                    ['emergency_request_id' => $requestId],
                    [
                        'provider_profile_id' => $providerId,
                        'provider_contract_id' => $contract->id,
                        'status_id' => $pendingId,
                        'gross_amount' => $gross,
                        'commission_percent_snapshot' => $rate,
                        'commission_amount' => round($gross * $rate / 100, 2),
                        'calculated_at' => now(),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        });

        return back()->with('status', __('Emergency request updated.'));
    }
}
