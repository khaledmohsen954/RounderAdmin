<?php

namespace App\Http\Controllers;

use App\Modules\Bookings\Enums\BookingStatusCode;
use App\Modules\Bookings\Enums\Operation;
use App\Modules\Bookings\Events\BookingsChanged;
use App\Modules\Bookings\Models\Booking;
use App\Modules\Bookings\Models\BookingStatus;
use App\Modules\Bookings\Models\BookingStatusHistory;
use App\Modules\Catalog\Models\Service;
use App\Modules\Catalog\Models\ServiceCategory;
use App\Modules\Identity\Enums\RoleCode;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\User;
use App\Modules\Properties\Models\City;
use App\Modules\Properties\Models\Home;
use App\Modules\Properties\Models\ResidentHome;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * متحكم الصفحة الرئيسية العامة؛ يربط واجهة العرض ببيانات الكتالوج ومعالجة طلبات الحجز المباشرة بأعلى كفاءة وأداء.
 */
class LandingController extends Controller
{
    /**
     * عرض الصفحة الرئيسية مع الأقسام والخدمات والمدن من الكاش وقاعدة البيانات بأعلى سرعة وأقل استهلاك للذاكرة.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $locale = app()->getLocale();

        // 1. أقسام الخدمات النشطة
        $categories = ServiceCategory::with(['translations'])
            ->where('is_active', true)
            ->get();

        // 2. الخدمات النشطة مع خدماتها الفرعية
        $services = Service::with([
            'translations',
            'category.translations',
            'subServices' => fn ($q) => $q->where('is_active', true)->with('translations'),
        ])
            ->where('is_active', true)
            ->get();

        // 3. جلب بيانات المناطق ومدنها مع كاش ذكي (24 ساعة) لتفادي تحميل 9,000+ كائن Eloquent في كل زيارة
        $regionsData = Cache::remember('landing_regions_data_' . $locale, 86400, function () use ($locale) {
            $rows = DB::table('cities')
                ->join('regions', 'cities.region_id', '=', 'regions.id')
                ->leftJoin('region_translations', function ($j) use ($locale) {
                    $j->on('regions.id', '=', 'region_translations.region_id')
                        ->where('region_translations.locale', $locale);
                })
                ->leftJoin('city_translations', function ($j) use ($locale) {
                    $j->on('cities.id', '=', 'city_translations.city_id')
                        ->where('city_translations.locale', $locale);
                })
                ->where('regions.is_active', 1)
                ->where('cities.is_active', 1)
                ->select(
                    'regions.id as region_id',
                    'regions.code as region_code',
                    'region_translations.name as region_name',
                    DB::raw('COALESCE(city_translations.name, cities.code) as city_name')
                )
                ->orderBy('regions.id')
                ->orderBy('cities.id')
                ->get();

            $map = [];
            foreach ($rows as $row) {
                if (!isset($map[$row->region_id])) {
                    $map[$row->region_id] = [
                        'id' => $row->region_id,
                        'code' => $row->region_code,
                        'name' => $row->region_name ?? $row->region_code,
                        'cities' => [],
                    ];
                }
                if (!empty($row->city_name)) {
                    $map[$row->region_id]['cities'][] = $row->city_name;
                }
            }

            return array_values($map);
        });

        // قائمة كافة المدن المتاحة بالمملكة للاستخدام العام والبحث
        $allCities = collect($regionsData)->pluck('cities')->flatten()->unique()->values()->all();

        // 4. تجهيز مصفوفة إعدادات الجافاسكريبت لربط المودال التفاعلي
        $clientConfig = [
            'locale' => 'ar-SA',
            'timeZone' => 'Asia/Riyadh',
            'csrfToken' => csrf_token(),
            'bookingEndpoint' => route('landing.booking.store'),
            'regions' => $regionsData,
            'cities' => $allCities,
            'propertyTypes' => ['شقة', 'فيلا', 'مكتب'],
            'businessTypes' => ['مكاتب وشركات', 'متاجر وفروع', 'عقارات سكنية', 'مطاعم ومقاهٍ'],
            'maintenanceTypes' => ['صيانة دورية', 'طلبات عند الحاجة', 'دورية وعند الحاجة'],
            'providerTypes' => ['فني مستقل', 'مؤسسة خدمات', 'شركة صيانة'],
            'experienceOptions' => ['أقل من سنة', '١ – ٣ سنوات', '٣ – ٥ سنوات', 'أكثر من ٥ سنوات'],
            'services' => $services->map(function ($service) use ($locale) {
                $name = $service->translations->firstWhere('locale', $locale)?->name ?? $service->code;
                $desc = $service->translations->firstWhere('locale', $locale)?->description;
                if (!$desc) {
                    $subsList = $service->subServices->take(3)->map(
                        fn ($s) => $s->translations->firstWhere('locale', $locale)?->name ?? $s->code
                    )->filter()->implode('، ');
                    $desc = $subsList ? 'يشمل: ' . $subsList : 'حلول وخدمات احترافية لبيتك';
                }

                // استخدام أيقونة الخدمة المسجلة ديناميكياً في قاعدة البيانات
                $icon = !empty($service->icon) ? $service->icon : 'home';
                $helpLabel = 'مشكلة في ' . $name;
                $items = $service->subServices->map(
                    fn ($sub) => $sub->translations->firstWhere('locale', $locale)?->name ?? $sub->code
                )->take(6)->values()->all();

                if (empty($items)) {
                    $items = ['فحص ومعاينة', 'صيانة وإصلاح', 'خدمات عند الطلب'];
                }

                return [
                    'id' => $service->code,
                    'service_id' => $service->id,
                    'category_code' => $service->category?->code ?? 'other',
                    'name' => $name,
                    'icon' => $icon,
                    'description' => $desc,
                    'helpLabel' => $helpLabel,
                    'items' => $items,
                    'base_price' => $service->base_price,
                ];
            })->values()->all(),
        ];

        return view('landing', compact('categories', 'services', 'clientConfig'));
    }

    /**
     * حفظ طلب حجز جديد مرسل من الصفحة الرئيسية في قاعدة البيانات داخل معاملة آمنة.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function storeBooking(Request $request): JsonResponse
    {
        $todayInRiyadh = Carbon::today('Asia/Riyadh')->format('Y-m-d');

        $validated = $request->validate([
            'service' => ['required', 'string'],
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'region' => ['nullable', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'property' => ['nullable', 'string', 'max:50'],
            'date' => ['required', 'date', 'after_or_equal:' . $todayInRiyadh],
            'time_from' => ['nullable', 'string', 'max:10'],
            'time_to' => ['nullable', 'string', 'max:10'],
            'details' => ['nullable', 'string', 'max:1000'],
        ], [
            'date.after_or_equal' => __('يجب أن يكون تاريخ الموعد اليوم أو تاريخاً مستقبلياً.'),
            'date.required' => __('يرجى تحديد تاريخ الموعد.'),
            'date.date' => __('صيغة تاريخ الموعد غير صحيحة.'),
            'name.required' => __('يرجى إدخال اسم العميل.'),
            'phone.required' => __('يرجى إدخال رقم الجوال.'),
            'city.required' => __('يرجى اختيار المدينة.'),
            'service.required' => __('يرجى اختيار الخدمة المطلوبة.'),
        ]);

        return DB::transaction(function () use ($validated) {
            // 1. تنظيف رقم الجوال وتحديد العميل
            $phone = preg_replace('/[^0-9+]/', '', trim($validated['phone']));
            $residentRoleId = Role::where('code', RoleCode::Resident->value)->value('id') ?? 5;

            $user = User::where('phone', $phone)->first();
            if (!$user) {
                $user = User::create([
                    'role_id' => $residentRoleId,
                    'name' => $validated['name'],
                    'phone' => $phone,
                    'locale' => 'ar',
                    'password' => bcrypt(Str::random(16)),
                ]);
            } else {
                if (!empty($validated['name']) && $user->name !== $validated['name']) {
                    $user->update(['name' => $validated['name']]);
                }
            }

            // 2. الخدمة المطلوبة
            $service = Service::where('code', $validated['service'])
                ->orWhere('id', $validated['service'])
                ->first();
            if (!$service) {
                $service = Service::where('is_active', true)->firstOrFail();
            }

            // 3. مطابقة المدينة
            $cityName = trim($validated['city']);
            $city = City::whereHas('translations', fn ($q) => $q->where('name', $cityName))
                ->orWhere('code', $cityName)
                ->first();

            // 4. العقار / موقع الحجز (إعادة استخدام عقار العميل لنفس المدينة أو إنشاء جديد)
            $propertyType = $validated['property'] ?? 'عقار سكني';
            $cityId = $city?->id ?? 4582; // الرياض كافتراضي عند عدم التحديد
            $regionName = $validated['region'] ?? null;

            $residentHome = ResidentHome::where('user_id', $user->id)
                ->where('residence_type', $propertyType)
                ->whereHas('home', fn ($q) => $q->where('city_id', $cityId))
                ->first();

            if (!$residentHome) {
                $home = Home::create([
                    'city_id' => $cityId,
                    'name' => $propertyType . ' - ' . $cityName,
                    'address' => ($regionName ? $regionName . ' - ' : '') . $cityName . ' (' . $propertyType . ')',
                    'property_code' => 'PROP-' . strtoupper(Str::random(8)),
                    'health_percent' => 100,
                    'association_balance' => 0.00,
                ]);

                $residentHome = ResidentHome::create([
                    'user_id' => $user->id,
                    'home_id' => $home->id,
                    'unit_label' => $propertyType,
                    'residence_type' => $propertyType,
                    'ownership_type' => 'owner',
                    'is_primary' => 1,
                ]);
            } else {
                $home = $residentHome->home;
            }

            // 5. موعد الحجز المحدد بدقة من العميل دون فرض أوقات افتراضية
            $timeFrom = $validated['time_from'] ?? null;
            $timeTo = $validated['time_to'] ?? null;
            try {
                $scheduledAt = !empty($timeFrom)
                    ? Carbon::parse($validated['date'] . ' ' . $timeFrom)
                    : Carbon::parse($validated['date']);
            } catch (\Throwable) {
                $scheduledAt = Carbon::parse($validated['date']);
            }

            // 6. حالة الحجز المبدئية (Pending)
            $status = BookingStatus::where('code', BookingStatusCode::Pending->value)->first();
            $statusId = $status?->id ?? 6;

            // 7. تدوين تفاصيل الطلب
            $notes = [];
            if (!empty($validated['details'])) {
                $notes[] = 'وصف الطلب: ' . $validated['details'];
            }
            $notes[] = 'العميل: ' . $validated['name'] . ' (جوال: ' . $phone . ')';
            $notes[] = 'الموقع: ' . ($regionName ? $regionName . ' · ' : '') . $cityName;
            $notes[] = 'نوع العقار: ' . $propertyType;

            $timeRangeText = ($timeFrom && $timeTo)
                ? ' (من ' . $timeFrom . ' إلى ' . $timeTo . ')'
                : ($timeFrom ? ' (الساعة ' . $timeFrom . ')' : '');
            $notes[] = 'الموعد المفضل: ' . $validated['date'] . $timeRangeText;

            // 8. إنشاء سجل الحجز الرسمي
            $booking = Booking::create([
                'resident_id' => $user->id,
                'resident_home_id' => $residentHome->id,
                'home_id' => $home->id,
                'service_id' => $service->id,
                'booking_status_id' => $statusId,
                'scheduled_at' => $scheduledAt,
                'total_amount' => $service->base_price ?? 0.00,
                'notes' => implode("\n", $notes),
            ]);

            // 9. إنشاء سجل حركة الحجز
            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'booking_status_id' => $statusId,
                'note' => 'تم إنشاء الطلب عبر الموقع الإلكتروني (Landing Page)',
                'created_at' => now(),
            ]);

            // 10. إطلاق حدث تحديث الحجوزات بعد نجاح المعاملة
            DB::afterCommit(fn () => event(new BookingsChanged(Operation::AdminOrderCreationStoreService, $user->id)));

            return response()->json([
                'success' => true,
                'booking_id' => $booking->id,
                'message' => 'تم استلام طلبك بنجاح! رقم الحجز الخاص بك هو #' . $booking->id,
            ]);
        });
    }
}
