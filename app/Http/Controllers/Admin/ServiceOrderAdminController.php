<?php

namespace App\Http\Controllers\Admin;

use App\Models\Booking;
use App\Models\BookingProviderAssignment;
use App\Models\BookingStatus;
use App\Models\BookingStatusHistory;
use App\Models\ProviderCommissionEntry;
use App\Models\ProviderCommissionStatus;
use App\Models\ProviderContract;
use App\Models\ProviderProfile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** إسناد طلبات الخدمات وتحديث حالاتها وقيود عمولات المزود. */
class ServiceOrderAdminController extends Controller
{
    public function index()
    {
        $locale = app()->getLocale();
        $orders = Booking::query()
            ->join('users as residents', 'bookings.resident_id', '=', 'residents.id')
            ->join('homes', 'bookings.home_id', '=', 'homes.id')
            ->join('services', 'bookings.service_id', '=', 'services.id')
            ->leftJoin('service_translations', function ($join) use ($locale) {
                $join->on('services.id', '=', 'service_translations.service_id')
                    ->where('service_translations.locale', '=', $locale);
            })
            ->join('booking_statuses', 'bookings.booking_status_id', '=', 'booking_statuses.id')
            ->join('booking_status_translations', function ($join) use ($locale) {
                $join->on('booking_statuses.id', '=', 'booking_status_translations.booking_status_id')
                    ->where('booking_status_translations.locale', '=', $locale);
            })
            ->leftJoin('booking_provider_assignments', 'bookings.id', '=', 'booking_provider_assignments.booking_id')
            ->leftJoin('provider_profiles', 'booking_provider_assignments.provider_profile_id', '=', 'provider_profiles.id')
            ->select('bookings.*', 'residents.name as resident_name', 'homes.name as home_name',
                'services.code as service_code', 'service_translations.name as service_name',
                'booking_statuses.code as status_code', 'booking_status_translations.name as status_name',
                'provider_profiles.id as provider_profile_id', 'provider_profiles.company_name as provider_name')
            ->orderByDesc('bookings.created_at')->paginate(25);

        $providers = ProviderProfile::query()->join('users', 'provider_profiles.user_id', '=', 'users.id')
            ->where('provider_profiles.is_approved', true)->select('provider_profiles.id', 'provider_profiles.company_name', 'users.name')
            ->orderBy('provider_profiles.company_name')->get();
        $statuses = BookingStatus::query()->join('booking_status_translations', function ($join) use ($locale) {
            $join->on('booking_statuses.id', '=', 'booking_status_translations.booking_status_id')
                ->where('booking_status_translations.locale', '=', $locale);
        })->select('booking_statuses.id', 'booking_statuses.code', 'booking_status_translations.name')->get();

        return view('admin.service-orders.index', compact('orders', 'providers', 'statuses'));
    }

    public function update(Request $request, int $bookingId)
    {
        $booking = Booking::query()->where('id', $bookingId)->first();
        abort_unless($booking, 404);
        $data = $request->validate([
            'provider_profile_id' => ['nullable', 'integer', 'exists:provider_profiles,id'],
            'booking_status_id' => ['required', 'integer', 'exists:booking_statuses,id'],
            'gross_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);
        $targetStatus = BookingStatus::query()->where('id', $data['booking_status_id'])->first();
        $assignment = BookingProviderAssignment::query()->where('booking_id', $bookingId)->first();
        $providerId = $data['provider_profile_id'] ?? $assignment?->provider_profile_id;
        $contract = null;

        if ($providerId) {
            abort_unless(ProviderProfile::query()->where('id', $providerId)->where('is_approved', true)->exists(), 422, __('Choose an approved provider.'));
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
        if ($targetStatus->code === 'confirmed' && !$providerId) {
            throw ValidationException::withMessages(['provider_profile_id' => __('Select a provider before confirming this booking.')]);
        }
        if ($targetStatus->code === 'completed' && (!$providerId || ($data['gross_amount'] ?? null) === null)) {
            throw ValidationException::withMessages(['gross_amount' => __('Assign a provider and enter the final amount before completing this booking.')]);
        }

        DB::transaction(function () use ($request, $booking, $bookingId, $data, $targetStatus, $providerId, $contract, $assignment) {
            if ($providerId && $contract) {
                $providerUserId = ProviderProfile::query()->where('id', $providerId)->value('user_id');
                BookingProviderAssignment::query()->updateOrCreate(
                    ['booking_id' => $bookingId],
                    [
                        'provider_profile_id' => $providerId,
                        'provider_contract_id' => $contract->id,
                        'assigned_by' => $request->user()->id,
                        'assigned_at' => $assignment?->assigned_at ?? now(),
                        'completed_at' => $targetStatus->code === 'completed' ? now() : null,
                        'provider_note' => $data['note'] ?? null,
                        'updated_at' => now(),
                        'created_at' => $assignment?->created_at ?? now(),
                    ]
                );
                Booking::query()->where('id', $bookingId)->update([
                    'provider_id' => $providerUserId,
                    'updated_at' => now(),
                ]);
            }

            Booking::query()->where('id', $bookingId)->update([
                'booking_status_id' => $targetStatus->id,
                'updated_at' => now(),
            ]);
            BookingStatusHistory::query()->create([
                'booking_id' => $bookingId,
                'booking_status_id' => $targetStatus->id,
                'changed_by' => $request->user()->id,
                'note' => $data['note'] ?? null,
                'created_at' => now(),
            ]);

            if ($targetStatus->code === 'completed' && $providerId && $contract) {
                $pendingId = ProviderCommissionStatus::query()->where('code', 'pending')->value('id');
                $gross = (float) $data['gross_amount'];
                $rate = (float) $contract->service_commission_percent;
                ProviderCommissionEntry::query()->updateOrCreate(
                    ['booking_id' => $bookingId],
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

        return back()->with('status', __('Service order updated and commission recalculated when completed.'));
    }
}
