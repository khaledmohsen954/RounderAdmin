<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ResidentHome;
use App\Models\ResidentSubscription;
use App\Models\ResidentSubscriptionService;
use App\Models\ServicePackage;
use App\Models\ServicePackageService;
use App\Models\SubscriptionStatus;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** عرض الباقات وإنشاء الاشتراك مع تثبيت السعر والحصص عند الشراء. */
class ResidentPackageController extends Controller
{
    /** تحميل العلاقات مسبقاً يمنع استعلاماً إضافياً لكل باقة وخدمة. */
    public function index()
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
        return response()->json(['data' => $data]);
    }

    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'resident_home_id' => [
                'required', 'integer',
                Rule::exists('resident_homes', 'id')->where('user_id', $request->user()->id),
            ],
            'service_package_id' => [
                'required', 'integer',
                Rule::exists('service_packages', 'id')->where('is_active', true),
            ],
        ]);

        // قفل العضوية يمنع إنشاء اشتراكين متزامنين لنفس الوحدة.
        return DB::transaction(function () use ($request, $data) {
            $membership = ResidentHome::query()->where('id', $data['resident_home_id'])
                ->where('user_id', $request->user()->id)->lockForUpdate()->first();
            abort_unless($membership, 404, 'Resident home not found.');

            $current = ResidentSubscription::query()
                ->where('resident_subscriptions.resident_home_id', $membership->id)
                ->where('resident_subscriptions.ends_at', '>', now())
                ->whereHas('status', fn ($query) => $query->whereIn('code', ['pending', 'active']))->exists();
            abort_if($current, 409, __('This home already has an active or pending package.'));

            $package = ServicePackage::query()->where('id', $data['service_package_id'])
                ->where('is_active', true)->first();
            abort_unless($package, 404);

            $statusCode = (float) $package->price === 0.0 ? 'active' : 'pending';
            $statusId = SubscriptionStatus::query()->where('code', $statusCode)->value('id');
            abort_unless($statusId, 500, 'Subscription statuses have not been seeded.');
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

            return response()->json([
                'data' => ResidentSubscription::query()->where('id', $subscriptionId)->first(),
                'payment_required' => (float) $package->price > 0,
            ], 201);
        });
    }

    public function cancel(Request $request, int $subscriptionId)
    {
        $subscription = ResidentSubscription::query()
            ->join('resident_homes', 'resident_subscriptions.resident_home_id', '=', 'resident_homes.id')
            ->join('subscription_statuses', 'resident_subscriptions.status_id', '=', 'subscription_statuses.id')
            ->where('resident_subscriptions.id', $subscriptionId)
            ->where('resident_homes.user_id', $request->user()->id)
            ->select('resident_subscriptions.*', 'subscription_statuses.code as status_code')->first();
        abort_unless($subscription, 404);

        abort_unless($subscription->status_code === 'pending', 409,
            'An active package cancellation must be handled by support.');

        $cancelledId = SubscriptionStatus::query()->where('code', 'cancelled')->value('id');
        ResidentSubscription::query()->where('id', $subscriptionId)->update([
            'status_id' => $cancelledId,
            'cancelled_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => __('Pending package request cancelled.')]);
    }
}
