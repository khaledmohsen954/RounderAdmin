<?php
namespace App\Modules\Providers\Services;
use App\Actions\CalculatePercentageAmount;
use App\Modules\Providers\Models\{ProviderContract, ProviderCommissionEntry, ProviderCommissionStatus};
use App\Modules\Providers\Enums\{CommissionSource, CommissionStatusCode, Operation};
use App\Modules\Providers\Events\ProvidersChanged;
use Illuminate\Support\Facades\DB;
/** المصدر الوحيد لاحتساب وحفظ عمولات المزود، سواء من الحجوزات أو الطوارئ. */
final class CommissionService
{
    /**
     * @param CalculatePercentageAmount $calculate
     * @return void
     */
    public function __construct(private readonly CalculatePercentageAmount $calculate) {}

    /**
     * تحفظ النسبة وقت الحساب مع القيد؛ لا تعتمد التقارير على نسبة العقد الحالية.
     * @param CommissionSource $source
     * @param int $sourceId
     * @param ProviderContract $contract
     * @param int $providerId
     * @param float $gross
     * @param int|null $actorId
     * @return ProviderCommissionEntry
     * @throws \Throwable إذا فشل الحفظ أو إعداد الحالة المرجعية.
     */
    public function record(CommissionSource $source, int $sourceId, ProviderContract $contract,
        int $providerId, float $gross, ?int $actorId): ProviderCommissionEntry
    {
        return DB::transaction(function () use ($source, $sourceId, $contract, $providerId, $gross, $actorId) {
            $rate = (float) ($source === CommissionSource::Booking
                ? $contract->service_commission_percent : $contract->emergency_commission_percent);
            $pending = ProviderCommissionStatus::where('code', CommissionStatusCode::Pending->value)->firstOrFail();
            $entry = ProviderCommissionEntry::updateOrCreate([$source->value => $sourceId], [
                'provider_profile_id' => $providerId, 'provider_contract_id' => $contract->id,
                'status_id' => $pending->id, 'gross_amount' => $gross,
                'commission_percent_snapshot' => $rate,
                'commission_amount' => $this->calculate->execute($gross, $rate),
                'calculated_at' => now(),
            ]);
            DB::afterCommit(fn () => event(new ProvidersChanged(Operation::RecordCommission, $actorId)));
            return $entry;
        });
    }
}
