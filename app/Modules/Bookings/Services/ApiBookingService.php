<?php

namespace App\Modules\Bookings\Services;
use App\Core\Exceptions\DomainViolation;
use App\Core\Enums\FailureReason;
use App\Modules\Bookings\Enums\BookingStatusCode;

use App\Modules\Bookings\Models\Booking;
use App\Modules\Bookings\Models\BookingProviderAssignment;
use App\Modules\Bookings\Models\BookingStatus;
use App\Modules\Catalog\Models\Service;
use App\Modules\Catalog\Models\SubService;
use App\Modules\Catalog\Models\SubServicePrice;
use App\Modules\Properties\Models\Home;
use App\Modules\Properties\Models\ResidentHome;
use App\Modules\Providers\Enums\ContractStatusCode;
use App\Modules\Providers\Models\ProviderContract;
use App\Modules\Providers\Models\ProviderProfile;

use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use App\Modules\Bookings\Events\BookingsChanged;
use App\Modules\Bookings\Enums\Operation;

/** قواعد عمل Bookings؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class ApiBookingService
{
    /**
     * @param BookingMediaStorage $mediaStorage
     */
    public function __construct(
        private readonly BookingMediaStorage $mediaStorage = new BookingMediaStorage()
    ) {}

    /**
     * قراءة بيانات المجال مع الحفاظ على قيود الوصول.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function index(array $input, ?User $actor): mixed
    {
        $query = Booking::with(['home', 'service.translations', 'subService.translations', 'status.translations', 'serviceProvider.user', 'rating', 'bookingMedia'])
            ->where('resident_id', $actor->id);

        if (!empty($input['status_id'])) {
            $query->where('booking_status_id', $input['status_id']);
        }

        if (!empty($input['status_code'])) {
            $query->whereHas('status', fn ($q) => $q->where('code', $input['status_code']));
        }

        if (!empty($input['resident_home_id'])) {
            $query->where('resident_home_id', $input['resident_home_id']);
        }

        $page = $query->latest()->paginate(max(1, min((int) ($input['per_page'] ?? 15), 50)));
        $locale = app()->getLocale();
        $items = $page->getCollection()->map(function (Booking $booking) use ($locale) {
            $code = $booking->status?->code ?? 'pending';
            $stageName = $this->formatStageName($code, $locale);
            $provider = $booking->serviceProvider;

            $providerData = $provider ? [
                'id' => $provider->id,
                'company_name' => $provider->company_name,
                'contact_name' => $provider->user?->name,
                'phone' => $provider->user?->phone,
                'email' => $provider->user?->email,
            ] : null;

            $existingRating = $booking->rating;

            return [
                'id' => $booking->id,
                'home_id' => $booking->home_id,
                'home_name' => $booking->home?->name,
                'service_id' => $booking->service_id,
                'service_name' => $booking->service?->translations->firstWhere('locale', $locale)?->name ?? $booking->service?->code,
                'sub_service_id' => $booking->sub_service_id,
                'sub_service_name' => $booking->subService?->translations->firstWhere('locale', $locale)?->name ?? $booking->subService?->code,
                'status_id' => $booking->booking_status_id,
                'status_code' => $code,
                'status_name' => $stageName,
                'scheduled_at' => $booking->scheduled_at,
                'total_amount' => $booking->total_amount,
                'service_provider_id' => $booking->service_provider_id,
                'service_provider_name' => $provider?->company_name,
                'service_provider' => $providerData,
                'provider' => $providerData,
                'can_cancel' => in_array($code, [BookingStatusCode::Pending->value, BookingStatusCode::Confirmed->value], true),
                'is_rated' => $existingRating !== null,
                'can_rate' => $existingRating === null,
                'rating' => $existingRating ? [
                    'id' => $existingRating->id,
                    'score' => $existingRating->score,
                    'comment' => $existingRating->comment,
                    'tags' => $this->formatTags($existingRating->tags ?? [], $locale),
                    'created_at' => $existingRating->created_at?->toIso8601String(),
                ] : null,
                'current_stage' => [
                    'id' => $booking->booking_status_id,
                    'code' => $code,
                    'name' => $stageName,
                ],
                'images' => $booking->bookingMedia->map(fn ($m) => [
                    'id' => $m->id,
                    'url' => $m->url,
                    'kind' => $m->kind,
                    'mime_type' => $m->mime_type,
                    'size_bytes' => $m->size_bytes,
                ])->values(),
            ];
        })->values();

        return ['data' => $items, 'meta' => [
            'current_page' => $page->currentPage(), 'last_page' => $page->lastPage(),
            'per_page' => $page->perPage(), 'total' => $page->total(),
        ]];
    }

    /**
     * إرجاع تفاصيل طلب محدد للساكن متضمناً مراحل التتبع (Stages) الأربع.
     * @param int $bookingId
     * @param ?User $actor
     * @return array
     */
    public function show(int $bookingId, ?User $actor): array
    {
        $booking = Booking::with([
            'home',
            'residentHome',
            'service.translations',
            'subService.translations',
            'status.translations',
            'serviceProvider.user',
            'rating',
            'bookingMedia',
            'statusHistory'
        ])
        ->where('resident_id', $actor->id)
        ->findOrFail($bookingId);

        $locale = app()->getLocale();
        $code = $booking->status?->code ?? 'pending';
        $stageName = $this->formatStageName($code, $locale);
        $provider = $booking->serviceProvider;

        $providerData = $provider ? [
            'id' => $provider->id,
            'company_name' => $provider->company_name,
            'contact_name' => $provider->user?->name,
            'phone' => $provider->user?->phone,
            'email' => $provider->user?->email,
        ] : null;

        $existingRating = $booking->rating;

        return [
            'data' => [
                'id' => $booking->id,
                'home_id' => $booking->home_id,
                'home_name' => $booking->home?->name,
                'unit_label' => $booking->residentHome?->unit_label,
                'service_id' => $booking->service_id,
                'service_name' => $booking->service?->translations->firstWhere('locale', $locale)?->name ?? $booking->service?->code,
                'sub_service_id' => $booking->sub_service_id,
                'sub_service_name' => $booking->subService?->translations->firstWhere('locale', $locale)?->name ?? $booking->subService?->code,
                'status_id' => $booking->booking_status_id,
                'status_code' => $code,
                'status_name' => $stageName,
                'current_stage' => [
                    'id' => $booking->booking_status_id,
                    'code' => $code,
                    'name' => $stageName,
                ],
                'scheduled_at' => $booking->scheduled_at,
                'total_amount' => $booking->total_amount,
                'service_provider_id' => $booking->service_provider_id,
                'service_provider_name' => $provider?->company_name,
                'service_provider' => $providerData,
                'provider' => $providerData,
                'is_rated' => $existingRating !== null,
                'can_rate' => $existingRating === null,
                'rating' => $existingRating ? [
                    'id' => $existingRating->id,
                    'score' => $existingRating->score,
                    'comment' => $existingRating->comment,
                    'tags' => $this->formatTags($existingRating->tags ?? [], $locale),
                    'created_at' => $existingRating->created_at?->toIso8601String(),
                ] : null,
                'notes' => $booking->notes,
                'can_cancel' => in_array($code, [BookingStatusCode::Pending->value, BookingStatusCode::Confirmed->value], true),
                'images' => $booking->bookingMedia->map(fn ($m) => [
                    'id' => $m->id,
                    'url' => $m->url,
                    'kind' => $m->kind,
                    'mime_type' => $m->mime_type,
                    'size_bytes' => $m->size_bytes,
                ])->values(),
                'created_at' => $booking->created_at?->toIso8601String(),
            ],
        ];
    }

    /**
     * ترجمة الأوصاف السريعة (Tags) بحسب لغة الطلب.
     * @param ?array $tags
     * @param string $locale
     * @return array<string>
     */
    public function formatTags(?array $tags, string $locale): array
    {
        if (empty($tags)) {
            return [];
        }

        $map = [
            'حسن التعامل' => ['ar' => 'حسن التعامل', 'en' => 'Good Communication', 'ur' => 'اچھا رویہ'],
            'good communication' => ['ar' => 'حسن التعامل', 'en' => 'Good Communication', 'ur' => 'اچھا رویہ'],
            'دقة الموعد' => ['ar' => 'دقة الموعد', 'en' => 'Punctuality', 'ur' => 'وقت کی پابندی'],
            'punctuality' => ['ar' => 'دقة الموعد', 'en' => 'Punctuality', 'ur' => 'وقت کی پابندی'],
            'جودة التنفيذ' => ['ar' => 'جودة التنفيذ', 'en' => 'Quality of Work', 'ur' => 'کام کا معیار'],
            'quality of work' => ['ar' => 'جودة التنفيذ', 'en' => 'Quality of Work', 'ur' => 'کام کا معیار'],
        ];

        return array_values(array_map(function ($tag) use ($map, $locale) {
            $key = mb_strtolower(trim($tag));
            return $map[$key][$locale] ?? $tag;
        }, $tags));
    }

    /**
     * ترجمة اسم المرحلة/الحالة بحسب تصميم الفيجما المعتمد لتطبيق الموبايل.
     * @param ?string $code
     * @param string $locale
     * @return string
     */
    public function formatStageName(?string $code, string $locale = 'ar'): string
    {
        $code = $code ?? BookingStatusCode::Pending->value;
        $names = [
            BookingStatusCode::Pending->value => [
                'ar' => 'تم إرسال طلبك',
                'en' => 'Order Submitted',
                'ur' => 'آپ کا آرڈر جمع کر دیا گیا ہے',
            ],
            BookingStatusCode::Confirmed->value => [
                'ar' => 'تم قبول الطلب',
                'en' => 'Order Accepted',
                'ur' => 'آرڈر قبول کر لیا گیا ہے',
            ],
            BookingStatusCode::InProgress->value => [
                'ar' => 'الفني في الطريق',
                'en' => 'Technician On The Way',
                'ur' => 'ٹیکنیشن راستے میں ہے',
            ],
            BookingStatusCode::Completed->value => [
                'ar' => 'اكتملت الخدمة',
                'en' => 'Service Completed',
                'ur' => 'سروس مکمل ہو گئی ہے',
            ],
            BookingStatusCode::Cancelled->value => [
                'ar' => 'تم إلغاء الحجز',
                'en' => 'Booking Cancelled',
                'ur' => 'بکنگ منسوخ کر دی گئی',
            ],
        ];

        return $names[$code][$locale] ?? ($names[$code]['ar'] ?? $code);
    }

    /**
     * جلب قائمة حالات الحجوزات لتطبيق الجوال مع ترجمة الاسم بحسب لغة المستخدم.
     * @return array<string, mixed>
     */
    public function statuses(): array
    {
        $locale = app()->getLocale();

        $statuses = BookingStatus::with('translations')
            ->orderBy('id')
            ->get()
            ->map(fn (BookingStatus $status) => [
                'id' => $status->id,
                'code' => $status->code,
                'name' => $this->formatStageName($status->code, $locale),
            ])
            ->values();

        return ['data' => $statuses];
    }

    /**
     * جلب قائمة مزودي الخدمة المعتمدين لتطبيق الجوال مع السعر بحسب المدينة والخدمة الفرعية.
     * @param array|int|null $input
     * @param ?User $actor
     * @return array<string, mixed>
     */
    public function serviceProviders(array|int|null $input = null, ?User $actor = null): array
    {
        if (is_int($input)) {
            $input = ['service_id' => $input];
        } else {
            $input = (array) $input;
        }

        $serviceId = !empty($input['service_id']) ? (int) $input['service_id'] : null;
        $subServiceRaw = $input['sub_service'] ?? $input['sub_service_id'] ?? null;
        $homeId = !empty($input['home_id']) ? (int) $input['home_id'] : null;

        $subServiceId = null;
        if (!empty($subServiceRaw)) {
            if (is_numeric($subServiceRaw)) {
                $subServiceId = (int) $subServiceRaw;
            } else {
                $subServiceId = SubService::where('code', $subServiceRaw)->value('id');
            }
        }

        if (!$serviceId && $subServiceId) {
            $serviceId = SubService::where('id', $subServiceId)->value('service_id');
        }

        $cityId = !empty($input['city_id']) ? (int) $input['city_id'] : null;
        if (!$cityId && $homeId) {
            $cityId = Home::where('id', $homeId)->value('city_id');
        } elseif (!$cityId && $actor) {
            $cityId = ResidentHome::with('home')->where('user_id', $actor->id)->first()?->home?->city_id;
        }

        // جلب الأسعار المخصصة إذا توفرت الخدمة الفرعية والمدينة
        $prices = collect();
        if ($subServiceId && $cityId) {
            $prices = SubServicePrice::query()
                ->where('sub_service_id', $subServiceId)
                ->where('is_active', true)
                ->where('approval_status', SubServicePrice::APPROVAL_APPROVED)
                ->whereHas('cities', fn ($q) => $q->where('cities.id', $cityId))
                ->get()
                ->keyBy('provider_profile_id');
        }

        $providers = ProviderProfile::query()
            ->where('is_approved', true)
            ->when($serviceId, fn ($query) => $query->whereHas('services', fn ($services) => $services->whereKey($serviceId)))
            ->with('user:id,name,phone')
            ->orderBy('company_name')
            ->get()
            ->map(function (ProviderProfile $p) use ($prices) {
                $priceRecord = $prices->get($p->id);

                return [
                    'id' => $p->id,
                    'company_name' => $p->company_name,
                    'contact_name' => $p->user?->name,
                    'phone' => $p->user?->phone,
                    'price' => $priceRecord ? (float) $priceRecord->price : null,
                    'price_id' => $priceRecord?->id,
                ];
            });

        return ['data' => $providers];
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function store(array $input, ?User $actor): mixed
    {
        return DB::transaction(function () use ($input, $actor) {
            $result = $this->performStore($input, $actor);
            DB::afterCommit(fn () => event(new BookingsChanged(Operation::ApiBookingStore, $actor?->id)));
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
    private function performStore(array $input, ?User $actor): mixed
    {
        $images = $input['images'] ?? [];
        $data = $input;
        unset($data['images']);

        $booking = DB::transaction(function () use ($input, $actor, $data, $images) {
            $pending = BookingStatus::where('code', BookingStatusCode::Pending->value)->firstOrFail();
            $service = Service::where('is_active', true)->lockForUpdate()->findOrFail($data['service_id']);

            $providerUserId = null;
            $serviceProviderId = null;
            $contract = null;

            if (!empty($data['service_provider_id'])) {
                $providerProfile = ProviderProfile::where('id', $data['service_provider_id'])
                    ->where('is_approved', true)
                    ->first();
                if ($providerProfile) {
                    $serviceProviderId = $providerProfile->id;
                    $providerUserId = $providerProfile->user_id;

                    $contract = ProviderContract::query()
                        ->where('provider_profile_id', $providerProfile->id)
                        ->where(function ($q) {
                            $q->whereHas('status', fn ($s) => $s->where('code', ContractStatusCode::Active->value))
                              ->orWhereNull('ends_on');
                        })
                        ->latest('id')
                        ->first();
                }
            }

            $residentHomeId = $data['resident_home_id'] ?? null;
            if (!$residentHomeId && !empty($data['home_id'])) {
                $residentHomeId = ResidentHome::where('home_id', $data['home_id'])
                    ->where('user_id', $actor->id)
                    ->value('id');
            }
            $homeId = $data['home_id'] ?? null;
            if (!$homeId && $residentHomeId) {
                $homeId = ResidentHome::where('id', $residentHomeId)->value('home_id');
            }

            $subServicePriceId = $data['sub_service_price_id'] ?? $data['price_id'] ?? null;
            $dynamicPrice = null;

            if ($subServicePriceId) {
                $priceRecord = SubServicePrice::query()
                    ->where('id', $subServicePriceId)
                    ->where('is_active', true)
                    ->where('approval_status', SubServicePrice::APPROVAL_APPROVED)
                    ->first();
                if ($priceRecord) {
                    $dynamicPrice = $priceRecord->price;
                    $subServicePriceId = $priceRecord->id;
                } else {
                    $subServicePriceId = null;
                }
            }

            if ($dynamicPrice === null && !empty($data['sub_service_id']) && $serviceProviderId) {
                $cityId = null;
                if (!empty($homeId)) {
                    $cityId = Home::where('id', $homeId)->value('city_id');
                } elseif ($residentHomeId) {
                    $cityId = ResidentHome::with('home')->find($residentHomeId)?->home?->city_id;
                }
                if ($cityId) {
                    $priceRecord = SubServicePrice::query()
                        ->where('provider_profile_id', $serviceProviderId)
                        ->where('sub_service_id', $data['sub_service_id'])
                        ->where('is_active', true)
                        ->where('approval_status', SubServicePrice::APPROVAL_APPROVED)
                        ->whereHas('cities', fn ($q) => $q->where('cities.id', $cityId))
                        ->first();
                    if ($priceRecord) {
                        $dynamicPrice = $priceRecord->price;
                        $subServicePriceId = $priceRecord->id;
                    }
                }
            }

            $booking = $actor->bookings()->create($data + [
                'home_id' => $homeId,
                'booking_status_id' => $pending->id,
                'total_amount' => $dynamicPrice ?? $service->base_price,
                'sub_service_price_id' => $subServicePriceId,
                'provider_id' => $providerUserId,
                'service_provider_id' => $serviceProviderId,
                'resident_home_id' => $residentHomeId,
            ]);

            if (!empty($images) && is_array($images)) {
                foreach ($images as $image) {
                    if ($image instanceof \Illuminate\Http\UploadedFile) {
                        $this->mediaStorage->store($booking, $image, $actor, 'photo');
                    }
                }
            }

            $booking->statusHistory()->create([
                'booking_status_id' => $pending->id,
                'changed_by' => $actor->id,
                'note' => 'Created by resident',
            ]);

            if ($serviceProviderId && $contract) {
                BookingProviderAssignment::query()->create([
                    'booking_id' => $booking->id,
                    'provider_profile_id' => $serviceProviderId,
                    'provider_contract_id' => $contract->id,
                    'assigned_at' => now(),
                    'provider_note' => $data['notes'] ?? null,
                ]);
            }

            return $booking;
        });

        return ['data' => $booking->load(['service.translations', 'subService.translations', 'status.translations', 'serviceProvider.user', 'home', 'bookingMedia'])];
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @param int $bookingId
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function cancel(array $input, ?User $actor, int $bookingId): mixed
    {
        return DB::transaction(function () use ($input, $actor, $bookingId) {
            $result = $this->performCancel($input, $actor, $bookingId);
            DB::afterCommit(fn () => event(new BookingsChanged(Operation::ApiBookingCancel, $actor?->id)));
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
    private function performCancel(array $input, ?User $actor, int $bookingId): mixed
    {
        $data = $input;

        
        $booking = DB::transaction(function () use ($input, $actor, $bookingId, $data) {
            $booking = $actor->bookings()->lockForUpdate()->findOrFail($bookingId);
            DomainViolation::unless(in_array($booking->status()->value('code'), [BookingStatusCode::Pending->value, BookingStatusCode::Confirmed->value], true), FailureReason::Conflict, __('This booking can no longer be cancelled.'));
            $cancelled = BookingStatus::where('code', BookingStatusCode::Cancelled->value)->firstOrFail();
            $booking->update([
                'booking_status_id' => $cancelled->id,
                'notes' => trim(($booking->notes ? $booking->notes."\n" : '').'Cancellation: '.$data['reason']),
            ]);
            $booking->statusHistory()->create([
                'booking_status_id' => $cancelled->id,
                'changed_by' => $actor->id, 'note' => $data['reason'],
            ]);

            return $booking;
        });

        return ['data' => $booking->fresh()->load('status.translations')];
    }
}
