<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PackageAdminController extends Controller
{
    public function index()
    {
        $services = DB::table('services')
            ->join('service_categories', 'services.service_category_id', '=', 'service_categories.id')
            ->leftJoin('service_category_translations', function ($join) {
                $join->on('service_categories.id', '=', 'service_category_translations.service_category_id')->where('service_category_translations.locale', '=', app()->getLocale());
            })
            ->leftJoin('service_translations', function ($join) {
                $join->on('services.id', '=', 'service_translations.service_id')
                    ->where('service_translations.locale', '=', app()->getLocale());
            })->select('services.*', 'service_category_translations.name as category_name', 'service_translations.name')
            ->orderBy('service_categories.code')->orderBy('services.code')->get();

        $categories = DB::table('service_categories')->join('service_category_translations', function ($join) {
            $join->on('service_categories.id', '=', 'service_category_translations.service_category_id')
                ->where('service_category_translations.locale', '=', app()->getLocale());
        })->where('service_categories.is_active', true)->select('service_categories.id', 'service_category_translations.name')->orderBy('name')->get();

        $cycles = DB::table('package_billing_cycles')->orderBy('months')->get();

        $packages = DB::table('service_packages')
            ->join('package_billing_cycles', 'service_packages.billing_cycle_id', '=', 'package_billing_cycles.id')
            ->leftJoin('service_package_translations', function ($join) {
                $join->on('service_packages.id', '=', 'service_package_translations.service_package_id')
                    ->where('service_package_translations.locale', '=', app()->getLocale());
            })->select('service_packages.*', 'package_billing_cycles.code as cycle_code', 'service_package_translations.name')
            ->selectSub(DB::table('service_package_services')->selectRaw('COUNT(*)')->whereColumn('service_package_services.service_package_id', 'service_packages.id'), 'services_count')
            ->orderByDesc('service_packages.id')->paginate(20);

        $subscriptions = DB::table('resident_subscriptions')
            ->join('resident_homes', 'resident_subscriptions.resident_home_id', '=', 'resident_homes.id')
            ->join('users', 'resident_homes.user_id', '=', 'users.id')
            ->join('homes', 'resident_homes.home_id', '=', 'homes.id')
            ->join('service_packages', 'resident_subscriptions.service_package_id', '=', 'service_packages.id')
            ->leftJoin('service_package_translations', function ($join) {
                $join->on('service_packages.id', '=', 'service_package_translations.service_package_id')
                    ->where('service_package_translations.locale', '=', app()->getLocale());
            })
            ->join('subscription_statuses', 'resident_subscriptions.status_id', '=', 'subscription_statuses.id')
            ->join('subscription_status_translations', function ($join) {
                $join->on('subscription_statuses.id', '=', 'subscription_status_translations.subscription_status_id')
                    ->where('subscription_status_translations.locale', '=', app()->getLocale());
            })
            ->select('resident_subscriptions.*', 'users.name as resident_name', 'homes.name as home_name',
                'service_package_translations.name as package_name', 'subscription_status_translations.name as status_name')
            ->orderByDesc('resident_subscriptions.created_at')->paginate(20, ['*'], 'subscriptions');

        $packageServices = DB::table('service_package_services')
            ->join('service_packages', 'service_package_services.service_package_id', '=', 'service_packages.id')
            ->join('services', 'service_package_services.service_id', '=', 'services.id')
            ->leftJoin('service_translations', function ($join) {
                $join->on('services.id', '=', 'service_translations.service_id')->where('service_translations.locale', '=', app()->getLocale());
            })->select('service_package_services.*', 'services.code as service_code', 'service_translations.name as service_name')
            ->get()->groupBy('service_package_id');

        return view('admin.packages.index', compact('services', 'categories', 'cycles', 'packages', 'packageServices', 'subscriptions'));
    }

    public function storeService(Request $request)
    {
        $data = $request->validate([
            'service_category_id' => ['required', 'integer', 'exists:service_categories,id'],
            'code' => ['required', 'alpha_dash', 'min:2', 'max:60', 'unique:services,code'],
            'base_price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'name_ar' => ['required', 'string', 'min:2', 'max:160'],
            'name_en' => ['required', 'string', 'min:2', 'max:160'],
            'name_ur' => ['required', 'string', 'min:2', 'max:160'],
            'description_ar' => ['nullable', 'string', 'max:5000'],
            'description_en' => ['nullable', 'string', 'max:5000'],
            'description_ur' => ['nullable', 'string', 'max:5000'],
        ]);

        DB::transaction(function () use ($data) {
            $serviceId = DB::table('services')->insertGetId([
                'service_category_id' => $data['service_category_id'],
                'code' => Str::lower($data['code']),
                'base_price' => $data['base_price'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            foreach (['ar', 'en', 'ur'] as $locale) {
                DB::table('service_translations')->insert([
                    'service_id' => $serviceId,
                    'locale' => $locale,
                    'name' => $data['name_' . $locale],
                    'description' => $data['description_' . $locale] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return back()->with('status', __('Service created successfully.'));
    }

    public function storePackage(Request $request)
    {
        $data = $request->validate([
            'billing_cycle_id' => ['required', 'integer', 'exists:package_billing_cycles,id'],
            'code' => ['required', 'alpha_dash', 'min:2', 'max:60', 'unique:service_packages,code'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'currency_code' => ['required', 'string', 'size:3', 'regex:/^[A-Z]{3}$/'],
            'duration_months' => ['required', 'integer', 'between:1,120'],
            'name_ar' => ['required', 'string', 'min:2', 'max:140'],
            'name_en' => ['required', 'string', 'min:2', 'max:140'],
            'name_ur' => ['required', 'string', 'min:2', 'max:140'],
            'description_ar' => ['nullable', 'string', 'max:5000'],
            'description_en' => ['nullable', 'string', 'max:5000'],
            'description_ur' => ['nullable', 'string', 'max:5000'],
            'services' => ['required', 'array', 'min:1', 'max:100'],
            'services.*.service_id' => ['required', 'integer', 'distinct', 'exists:services,id'],
            'services.*.included_quantity' => ['nullable', 'integer', 'between:1,1000'],
            'services.*.is_unlimited' => ['required', 'boolean'],
        ]);

        foreach ($data['services'] as $index => $service) {
            if (!$service['is_unlimited'] && empty($service['included_quantity'])) {
                throw ValidationException::withMessages([
                    "services.$index.included_quantity" => __('Set an included quantity or mark this service unlimited.'),
                ]);
            }
        }

        DB::transaction(function () use ($data) {
            $packageId = DB::table('service_packages')->insertGetId([
                'billing_cycle_id' => $data['billing_cycle_id'],
                'code' => Str::lower($data['code']),
                'price' => $data['price'],
                'currency_code' => $data['currency_code'],
                'duration_months' => $data['duration_months'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            foreach (['ar', 'en', 'ur'] as $locale) {
                DB::table('service_package_translations')->insert([
                    'service_package_id' => $packageId,
                    'locale' => $locale,
                    'name' => $data['name_' . $locale],
                    'description' => $data['description_' . $locale] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            foreach ($data['services'] as $included) {
                DB::table('service_package_services')->insert([
                    'service_package_id' => $packageId,
                    'service_id' => $included['service_id'],
                    'included_quantity' => $included['is_unlimited'] ? null : $included['included_quantity'],
                    'is_unlimited' => $included['is_unlimited'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return back()->with('status', __('Package created successfully.'));
    }
}
