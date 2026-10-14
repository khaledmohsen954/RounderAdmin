<?php

namespace App\Modules\Bookings\Services;
use App\Modules\Providers\Services\CommissionService;
use App\Modules\Providers\Enums\CommissionSource;
use App\Core\Exceptions\DomainViolation;
use App\Core\Enums\FailureReason;
use App\Modules\Providers\Enums\CommissionStatusCode;
use App\Modules\Providers\Enums\ContractStatusCode;
use App\Modules\Bookings\Enums\BookingStatusCode;

use App\Modules\Bookings\Models\Booking;
use App\Modules\Bookings\Models\BookingProviderAssignment;
use App\Modules\Bookings\Models\BookingStatus;
use App\Modules\Bookings\Models\BookingStatusHistory;
use App\Modules\Providers\Models\ProviderCommissionEntry;
use App\Modules\Providers\Models\ProviderCommissionStatus;
use App\Modules\Providers\Models\ProviderContract;
use App\Modules\Providers\Models\ProviderProfile;
use Illuminate\Validation\ValidationException;
use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use App\Modules\Bookings\Events\BookingsChanged;
use App\Modules\Bookings\Enums\Operation;

/** قواعد عمل Bookings؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class AdminServiceOrderAdminService
{
    /**
     * @param CommissionService $commissions
     * @return void
     */
    public function __construct(private readonly CommissionService $commissions) {}


    /**
     * قراءة بيانات المجال مع الحفاظ على قيود الوصول.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function index(array $input, ?User $actor): mixed
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

        return compact('orders', 'providers', 'statuses');
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @param int $bookingId
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function update(array $input, ?User $actor, int $bookingId): mixed
    {
        return DB::transaction(function () use ($input, $actor, $bookingId) {
            $result = $this->performUpdate($input, $actor, $bookingId);
            DB::afterCommit(fn () => event(new BookingsChanged(Operation::AdminOrderAdminServiceUpdate, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param ?User $actor
     * @param int $bookingId
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performUpdate(array $input, ?User $actor, int $bookingId): mixed
    {
        $data = $input;

        $booking = Booking::query()->where('id', $bookingId)->first();
        DomainViolation::unless($booking, FailureReason::NotFound);
        
        $targetStatus = BookingStatus::query()->where('id', $data['booking_status_id'])->first();
        $assignment = BookingProviderAssignment::query()->where('booking_id', $bookingId)->first();
        $providerId = $data['provider_profile_id'] ?? $assignment?->provider_profile_id;
        $contract = null;

        if ($providerId) {
            DomainViolation::unless(ProviderProfile::query()->where('id', $providerId)->where('is_approved', true)->exists(), FailureReason::InvalidInput, __('Choose an approved provider.'));
            if ($assignment && (int) $assignment->provider_profile_id === (int) $providerId) {
                $contract = ProviderContract::query()->where('id', $assignment->provider_contract_id)->first();
            } else {
                $contract = ProviderContract::query()
                    ->join('provider_contract_statuses', 'provider_contracts.status_id', '=', 'provider_contract_statuses.id')
                    ->where('provider_contracts.provider_profile_id', $providerId)
                    ->where('provider_contract_statuses.code', ContractStatusCode::Active->value)
                    ->whereDate('provider_contracts.starts_on', '<=', today())
                    ->where(function ($query) {
                        $query->whereNull('provider_contracts.ends_on')->orWhereDate('provider_contracts.ends_on', '>=', today());
                    })->orderByDesc('provider_contracts.starts_on')->select('provider_contracts.*')->first();
            }
            DomainViolation::unless($contract, FailureReason::InvalidInput, __('Provider needs an active contract before assignment.'));
        }
        if ($targetStatus->code === BookingStatusCode::Confirmed->value && !$providerId) {
            throw ValidationException::withMessages(['provider_profile_id' => __('Select a provider before confirming this booking.')]);
        }
        if ($targetStatus->code === BookingStatusCode::Completed->value && (!$providerId || ($data['gross_amount'] ?? null) === null)) {
            throw ValidationException::withMessages(['gross_amount' => __('Assign a provider and enter the final amount before completing this booking.')]);
        }

        DB::transaction(function () use ($input, $actor, $booking, $bookingId, $data, $targetStatus, $providerId, $contract, $assignment) {
            if ($providerId && $contract) {
                $providerUserId = ProviderProfile::query()->where('id', $providerId)->value('user_id');
                BookingProviderAssignment::query()->updateOrCreate(
                    ['booking_id' => $bookingId],
                    [
                        'provider_profile_id' => $providerId,
                        'provider_contract_id' => $contract->id,
                        'assigned_by' => $actor->id,
                        'assigned_at' => $assignment?->assigned_at ?? now(),
                        'completed_at' => $targetStatus->code === BookingStatusCode::Completed->value ? now() : null,
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
                'changed_by' => $actor->id,
                'note' => $data['note'] ?? null,
                'created_at' => now(),
            ]);

            if ($targetStatus->code === BookingStatusCode::Completed->value && $providerId && $contract) {
                $this->commissions->record(CommissionSource::Booking, $bookingId, $contract,
                    (int) $providerId, (float) $data['gross_amount'], $actor?->id);
            }
        });

        return __('Service order updated and commission recalculated when completed.');
    }
}
