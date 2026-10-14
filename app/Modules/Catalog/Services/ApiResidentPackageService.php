<?php

namespace App\Modules\Catalog\Services;
use App\Core\Exceptions\DomainViolation;
use App\Core\Enums\FailureReason;
use App\Modules\Catalog\Enums\SubscriptionStatusCode;

use App\Modules\Properties\Models\ResidentHome;
use App\Modules\Catalog\Models\ResidentSubscription;
use App\Modules\Catalog\Models\ResidentSubscriptionService;
use App\Modules\Catalog\Models\ServicePackage;

use App\Modules\Catalog\Models\SubscriptionStatus;
use Illuminate\Support\Str;

use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use App\Modules\Catalog\Events\CatalogChanged;
use App\Modules\Catalog\Enums\Operation;

/** قواعد عمل Catalog؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class ApiResidentPackageService
{

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
        $packages = ServicePackage::query()
            ->with([
                'billingCycle',
                'translations' => fn ($query) => $query->where('locale', $locale),
                'includedServices.service.translations' => fn ($query) => $query->where('locale', $locale),
            ])
            ->where('is_active', true)->orderBy('price')->get();

        // نحافظ على أسماء حقول الـAPI الحالية رغم تغيير طبقة الوصول للبيانات.
        $data = $packages->map(fn (ServicePackage $package) => [
            'id' => $package->id,
            'code' => $package->code,
            'name' => $package->translations->first()?->name ?? $package->code,
            'description' => $package->translations->first()?->description,
            'price' => $package->price,
            'currency_code' => $package->currency_code,
            'duration_months' => $package->duration_months,
            'billing_cycle' => [
                'id' => $package->billingCycle->id,
                'code' => $package->billingCycle->code,
                'months' => $package->billingCycle->months,
            ],
            'services' => $package->includedServices->map(fn ($included) => [
                'service_package_id' => $package->id,
                'id' => $included->service->id,
                'code' => $included->service->code,
                'name' => $included->service->translations->first()?->name,
                'description' => $included->service->translations->first()?->description,
                'included_quantity' => $included->included_quantity,
                'is_unlimited' => $included->is_unlimited,
            ])->values(),
        ]);
        return ['data' => $data];
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function subscribe(array $input, ?User $actor): mixed
    {
        return DB::transaction(function () use ($input, $actor) {
            $result = $this->performSubscribe($input, $actor);
            DB::afterCommit(fn () => event(new CatalogChanged(Operation::ApiResidentPackageSubscribe, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performSubscribe(array $input, ?User $actor): mixed
    {
        $data = $input;

        

        // قفل العضوية يمنع إنشاء اشتراكين متزامنين لنفس الوحدة.
        return DB::transaction(function () use ($input, $actor, $data) {
            $membership = ResidentHome::query()->where('id', $data['resident_home_id'])
                ->where('user_id', $actor->id)->lockForUpdate()->first();
            DomainViolation::unless($membership, FailureReason::NotFound, 'Resident home not found.');

            $current = ResidentSubscription::query()
                ->where('resident_subscriptions.resident_home_id', $membership->id)
                ->where('resident_subscriptions.ends_at', '>', now())
                ->whereHas('status', fn ($query) => $query->whereIn('code', [SubscriptionStatusCode::Pending->value, SubscriptionStatusCode::Active->value]))->exists();
            DomainViolation::rejectIf($current, FailureReason::Conflict, __('This home already has an active or pending package.'));

            $package = ServicePackage::query()->where('id', $data['service_package_id'])
                ->where('is_active', true)->first();
            DomainViolation::unless($package, FailureReason::NotFound);

            $statusCode = (float) $package->price === 0.0 ? SubscriptionStatusCode::Active->value : SubscriptionStatusCode::Pending->value;
            $statusId = SubscriptionStatus::query()->where('code', $statusCode)->value('id');
            DomainViolation::unless($statusId, FailureReason::Configuration, 'Subscription statuses have not been seeded.');
            $starts = now();
            $ends = $starts->copy()->addMonths((int) $package->duration_months);
            $subscriptionId = ResidentSubscription::query()->create([
                'resident_home_id' => $membership->id,
                'service_package_id' => $package->id,
                'status_id' => $statusId,
                'reference' => 'SUB-' . Str::upper(Str::random(12)),
                'price_snapshot' => $package->price,
                'currency_code' => $package->currency_code,
                'starts_at' => $starts,
                'ends_at' => $ends,
                'created_at' => now(),
                'updated_at' => now(),
            ])->getKey();

            // ننسخ الحصص لحماية حقوق العميل عند تعديل الباقة الأصلية لاحقاً.
            foreach ($package->includedServices as $service) {
                ResidentSubscriptionService::query()->create([
                    'resident_subscription_id' => $subscriptionId,
                    'service_id' => $service->service_id,
                    'included_quantity' => $service->included_quantity,
                    'used_quantity' => 0,
                    'is_unlimited' => $service->is_unlimited,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return [
                'data' => ResidentSubscription::query()->where('id', $subscriptionId)->first(),
                'payment_required' => (float) $package->price > 0,
            ];
        });
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @param int $subscriptionId
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function cancel(array $input, ?User $actor, int $subscriptionId): mixed
    {
        return DB::transaction(function () use ($input, $actor, $subscriptionId) {
            $result = $this->performCancel($input, $actor, $subscriptionId);
            DB::afterCommit(fn () => event(new CatalogChanged(Operation::ApiResidentPackageCancel, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param ?User $actor
     * @param int $subscriptionId
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performCancel(array $input, ?User $actor, int $subscriptionId): mixed
    {
        $subscription = ResidentSubscription::query()
            ->join('resident_homes', 'resident_subscriptions.resident_home_id', '=', 'resident_homes.id')
            ->join('subscription_statuses', 'resident_subscriptions.status_id', '=', 'subscription_statuses.id')
            ->where('resident_subscriptions.id', $subscriptionId)
            ->where('resident_homes.user_id', $actor->id)
            ->select('resident_subscriptions.*', 'subscription_statuses.code as status_code')->first();
        DomainViolation::unless($subscription, FailureReason::NotFound);

        DomainViolation::unless($subscription->status_code === SubscriptionStatusCode::Pending->value, FailureReason::Conflict, 'An active package cancellation must be handled by support.');

        $cancelledId = SubscriptionStatus::query()->where('code', SubscriptionStatusCode::Cancelled->value)->value('id');
        ResidentSubscription::query()->where('id', $subscriptionId)->update([
            'status_id' => $cancelledId,
            'cancelled_at' => now(),
            'updated_at' => now(),
        ]);

        return ['message' => __('Pending package request cancelled.')];
    }
}
