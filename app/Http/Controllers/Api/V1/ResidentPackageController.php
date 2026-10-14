<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ResidentPackageController extends Controller
{
    public function index()
    {
        $locale = app()->getLocale();
        $packages = DB::table('service_packages')
            ->join('package_billing_cycles', 'service_packages.billing_cycle_id', '=', 'package_billing_cycles.id')
            ->leftJoin('service_package_translations', function ($join) use ($locale) {
                $join->on('service_packages.id', '=', 'service_package_translations.service_package_id')
                    ->where('service_package_translations.locale', '=', $locale);
            })->where('service_packages.is_active', true)
            ->select('service_packages.id', 'service_packages.code', 'service_packages.price',
                'service_packages.currency_code', 'service_packages.duration_months',
                'package_billing_cycles.id as billing_cycle_id', 'package_billing_cycles.code as billing_cycle_code',
                'package_billing_cycles.months as billing_cycle_months', 'service_package_translations.name',
                'service_package_translations.description')->orderBy('service_packages.price')->get();

        $included = DB::table('service_package_services')
            ->join('services', 'service_package_services.service_id', '=', 'services.id')
            ->leftJoin('service_translations', function ($join) use ($locale) {
                $join->on('services.id', '=', 'service_translations.service_id')
                    ->where('service_translations.locale', '=', $locale);
            })->select('service_package_services.service_package_id', 'services.id', 'services.code',
                'service_translations.name', 'service_translations.description', 'service_package_services.included_quantity',
                'service_package_services.is_unlimited')->get()->groupBy('service_package_id');

        $data = $packages->map(fn ($package) => [
            'id' => $package->id,
            'code' => $package->code,
            'name' => $package->name ?? $package->code,
            'description' => $package->description,
            'price' => $package->price,
            'currency_code' => $package->currency_code,
            'duration_months' => $package->duration_months,
            'billing_cycle' => [
                'id' => $package->billing_cycle_id,
                'code' => $package->billing_cycle_code,
                'months' => $package->billing_cycle_months,
            ],
            'services' => $included->get($package->id, collect())->values(),
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

        return DB::transaction(function () use ($request, $data) {
            $membership = DB::table('resident_homes')->where('id', $data['resident_home_id'])
                ->where('user_id', $request->user()->id)->lockForUpdate()->first();
            abort_unless($membership, 404, 'Resident home not found.');

            $current = DB::table('resident_subscriptions')
                ->join('subscription_statuses', 'resident_subscriptions.status_id', '=', 'subscription_statuses.id')
                ->where('resident_subscriptions.resident_home_id', $membership->id)
                ->where('resident_subscriptions.ends_at', '>', now())
                ->whereIn('subscription_statuses.code', ['pending', 'active'])->exists();
            abort_if($current, 409, __('This home already has an active or pending package.'));

            $package = DB::table('service_packages')->where('id', $data['service_package_id'])
                ->where('is_active', true)->first();
            abort_unless($package, 404);

            $statusCode = (float) $package->price === 0.0 ? 'active' : 'pending';
            $statusId = DB::table('subscription_statuses')->where('code', $statusCode)->value('id');
            abort_unless($statusId, 500, 'Subscription statuses have not been seeded.');
            $starts = now();
            $ends = $starts->copy()->addMonths((int) $package->duration_months);
            $subscriptionId = DB::table('resident_subscriptions')->insertGetId([
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
            ]);

            foreach (DB::table('service_package_services')->where('service_package_id', $package->id)->get() as $service) {
                DB::table('resident_subscription_services')->insert([
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
                'data' => DB::table('resident_subscriptions')->where('id', $subscriptionId)->first(),
                'payment_required' => (float) $package->price > 0,
            ], 201);
        });
    }

    public function cancel(Request $request, int $subscriptionId)
    {
        $subscription = DB::table('resident_subscriptions')
            ->join('resident_homes', 'resident_subscriptions.resident_home_id', '=', 'resident_homes.id')
            ->join('subscription_statuses', 'resident_subscriptions.status_id', '=', 'subscription_statuses.id')
            ->where('resident_subscriptions.id', $subscriptionId)
            ->where('resident_homes.user_id', $request->user()->id)
            ->select('resident_subscriptions.*', 'subscription_statuses.code as status_code')->first();
        abort_unless($subscription, 404);

        abort_unless($subscription->status_code === 'pending', 409,
            'An active package cancellation must be handled by support.');

        $cancelledId = DB::table('subscription_statuses')->where('code', 'cancelled')->value('id');
        DB::table('resident_subscriptions')->where('id', $subscriptionId)->update([
            'status_id' => $cancelledId,
            'cancelled_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => __('Pending package request cancelled.')]);
    }
}
