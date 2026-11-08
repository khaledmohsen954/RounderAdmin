<?php

namespace App\Modules\Providers\Services;

use App\Core\AdminActor;
use App\Modules\Bookings\Enums\BookingStatusCode;
use App\Modules\Bookings\Enums\Operation as BookingOperation;
use App\Modules\Bookings\Models\Booking;
use App\Modules\Bookings\Models\BookingProviderAssignment;
use App\Modules\Bookings\Models\BookingRating;
use App\Modules\Bookings\Models\BookingStatus;
use App\Modules\Bookings\Models\BookingStatusHistory;
use App\Modules\Bookings\Services\AdminServiceOrderAdminService;
use App\Modules\Catalog\Models\Service;
use App\Modules\Catalog\Models\SubService;
use App\Modules\Catalog\Models\SubServicePrice;
use App\Modules\Properties\Models\Home;
use App\Modules\Emergencies\Models\EmergencyProviderAssignment;
use App\Modules\Identity\Enums\RoleCode;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Services\ProviderAccountService;
use App\Modules\Properties\Models\City;
use App\Modules\Properties\Models\Region;
use App\Modules\Properties\Models\ResidentHome;
use App\Modules\Providers\Enums\ContractStatusCode;
use App\Modules\Providers\Models\ProviderContract;
use App\Modules\Providers\Models\ProviderContractStatus;
use App\Modules\Providers\Models\ProviderProfile;
use App\Modules\Emergencies\Models\EmergencyRequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProviderPortalService
{
    public const PERMISSIONS = [
        'dashboard.view',
        'orders.view',
        'orders.create',
        'orders.manage',
        'prices.view',
        'prices.manage',
        'emergencies.view',
        'users.manage',
        'finance.view',
        'ratings.view',
        'profile.manage',
        'contract.view',
    ];

    public function __construct(
        private readonly ProviderAccountService $accounts,
        private readonly AdminServiceOrderAdminService $orderAdmin,
    ) {
    }

    public function profileFor(User $user): ?ProviderProfile
    {
        return $user->provider_profile_id
            ? ProviderProfile::query()->find($user->provider_profile_id)
            : ProviderProfile::query()->where('user_id', $user->id)->first();
    }

    public function acceptContract(ProviderProfile $profile): void
    {
        if (!$profile->is_approved) {
            throw ValidationException::withMessages(['contract' => __('Your provider application is still awaiting admin approval.')]);
        }

        $contract = $profile->contracts()->with('status')->latest('id')->first();
        if (!$contract || $contract->status?->code !== ContractStatusCode::Active->value
            || ($contract->ends_on && $contract->ends_on->lt(today())) || blank($contract->terms)) {
            throw ValidationException::withMessages(['contract' => __('The provider contract is not ready for acceptance.')]);
        }

        $profile->update(['contract_accepted_at' => now()]);
    }

    public function contractFor(ProviderProfile $profile): ?ProviderContract
    {
        return $profile->contracts()->with('status')->latest('id')->first();
    }

    public function financialAccount(ProviderProfile $profile): array
    {
        $entries = $profile->providerCommissionEntries()->with([
            'status',
            'booking.service.translations',
            'emergencyRequest.emergencyType.translations',
        ])->latest('calculated_at');

        return [
            'entries' => $entries->paginate(20)->withQueryString(),
            'totals' => [
                'gross' => (float) $profile->providerCommissionEntries()->sum('gross_amount'),
                'commission' => (float) $profile->providerCommissionEntries()->sum('commission_amount'),
                'settled' => (float) $profile->providerCommissionEntries()
                    ->whereHas('status', fn (Builder $query) => $query->where('code', 'settled'))
                    ->sum('commission_amount'),
            ],
        ];
    }

    public function ratings(ProviderProfile $profile): array
    {
        $ratings = BookingRating::query()
            ->whereHas('booking', fn (Builder $query) => $query
                ->where('service_provider_id', $profile->id)
                ->orWhereHas('assignment', fn (Builder $assignment) => $assignment->where('provider_profile_id', $profile->id)))
            ->with(['resident', 'booking.service.translations'])
            ->latest('id');

        return [
            'averageScore' => (float) (clone $ratings)->avg('score'),
            'ratingsCount' => (clone $ratings)->count(),
            'ratings' => $ratings->paginate(20)->withQueryString(),
        ];
    }

    public function profileSettings(ProviderProfile $profile): array
    {
        $services = $profile->services()->with('translations')->orderBy('services.code')->get();
        $availableServices = Service::query()->where('is_active', true)
            ->whereNotIn('id', $services->modelKeys())
            ->with('translations')->orderBy('code')->get();
        $ordersByService = Booking::query()
            ->whereIn('service_id', $services->modelKeys())
            ->where(fn (Builder $query) => $query->where('service_provider_id', $profile->id)
                ->orWhereHas('assignment', fn (Builder $assignment) => $assignment->where('provider_profile_id', $profile->id)))
            ->whereHas('status', fn (Builder $status) => $status->whereIn('code', [
                BookingStatusCode::Pending->value,
                BookingStatusCode::Confirmed->value,
                BookingStatusCode::InProgress->value,
            ]))
            ->select('service_id')->distinct()->pluck('service_id')->mapWithKeys(fn ($id) => [$id => true]);

        return compact('profile', 'services', 'availableServices', 'ordersByService');
    }

    public function updateProfile(ProviderProfile $profile, array $input): void
    {
        $profile->update($input);
    }

    public function addService(ProviderProfile $profile, int $serviceId): void
    {
        $service = Service::query()->whereKey($serviceId)->where('is_active', true)->firstOrFail();
        if ($profile->services()->whereKey($service->id)->exists()) {
            throw ValidationException::withMessages(['service_id' => __('This service is already linked to your provider profile.')]);
        }

        $profile->services()->attach($service->id);
    }

    public function removeService(ProviderProfile $profile, int $serviceId): void
    {
        abort_unless($profile->services()->whereKey($serviceId)->exists(), 404);
        $hasOrders = Booking::query()->where('service_id', $serviceId)
            ->where(fn (Builder $query) => $query->where('service_provider_id', $profile->id)
                ->orWhereHas('assignment', fn (Builder $assignment) => $assignment->where('provider_profile_id', $profile->id)))
            ->whereHas('status', fn (Builder $status) => $status->whereIn('code', [
                BookingStatusCode::Pending->value,
                BookingStatusCode::Confirmed->value,
                BookingStatusCode::InProgress->value,
            ]))
            ->exists();
        if ($hasOrders) {
            throw ValidationException::withMessages(['service_id' => __('A service with an active order cannot be removed.')]);
        }

        $profile->services()->detach($serviceId);
    }

    public function isOwner(User $user, ProviderProfile $profile): bool
    {
        return (int) $profile->user_id === (int) $user->id;
    }

    public function allows(User $user, ProviderProfile $profile, string $permission): bool
    {
        $permissions = $user->permissions;
        if ($permissions === null && $this->isOwner($user, $profile)) {
            $permissions = self::PERMISSIONS;
        }

        return in_array($permission, $permissions ?? [], true);
    }

    public function dashboard(ProviderProfile $profile): array
    {
        $orders = $this->providerOrders($profile);
        $services = $profile->services()->where('services.is_active', true)
            ->with('translations')->orderBy('services.code')->get();
        $emergencies = $this->emergencyAssignments($profile)->latest('emergency_provider_assignments.assigned_at')->limit(5)->get();

        $visibleBookingStatuses = BookingStatus::query()
            ->where('show_in_provider_portal', true)
            ->with('translations')
            ->orderBy('id')
            ->get()
            ->map(function (BookingStatus $st) use ($orders) {
                $st->provider_orders_count = (clone $orders)->where('booking_status_id', $st->id)->count();
                return $st;
            });

        return [
            'profile' => $profile,
            'ordersCount' => (clone $orders)->count(),
            'pendingCount' => (clone $orders)->whereHas('status', fn (Builder $query) => $query->where('code', BookingStatusCode::Pending->value))->count(),
            'activeCount' => (clone $orders)->whereHas('status', fn (Builder $query) => $query->whereIn('code', [BookingStatusCode::Confirmed->value, BookingStatusCode::InProgress->value]))->count(),
            'completedCount' => (clone $orders)->whereHas('status', fn (Builder $query) => $query->where('code', BookingStatusCode::Completed->value))->count(),
            'visibleBookingStatuses' => $visibleBookingStatuses,
            'orders' => $this->ordersQuery($profile)->latest('bookings.created_at')->limit(8)->get(),
            'services' => $services,
            'emergencies' => $emergencies,
        ];
    }

    public function emergencies(ProviderProfile $profile): array
    {
        $visibleStatuses = EmergencyRequestStatus::query()
            ->where('show_in_provider_portal', true)
            ->with('translations')
            ->orderBy('id')
            ->get();

        $selectedStatus = request()->query('status');
        $query = $this->emergencyAssignments($profile);
        if ($selectedStatus && $visibleStatuses->contains('code', $selectedStatus)) {
            $query->whereHas('emergencyRequest.status', fn (Builder $q) => $q->where('code', $selectedStatus));
        }

        return [
            'emergencies' => $query
                ->latest('emergency_provider_assignments.assigned_at')
                ->paginate(20)->withQueryString(),
            'visibleStatuses' => $visibleStatuses,
            'selectedStatus' => $selectedStatus,
        ];
    }

    public function priceForm(ProviderProfile $profile, ?int $priceId = null): array
    {
        $serviceIds = $profile->services()->where('services.is_active', true)->select('services.id');
        $services = $profile->services()->where('services.is_active', true)
            ->with('translations')->orderBy('services.code')->get();
        $subServices = SubService::query()->whereIn('service_id', $serviceIds)
            ->where('is_active', true)->with(['translations', 'service.translations'])
            ->orderBy('sort_order')->orderBy('id')->get();
        $regions = Region::query()->available()->with([
            'translations',
            'cities' => fn ($query) => $query->where('is_active', true)->with('translations')->orderBy('id'),
        ])->orderBy('id')->get();
        $price = null;

        if ($priceId !== null) {
            $price = $profile->prices()->whereKey($priceId)
                ->where('approval_status', SubServicePrice::APPROVAL_REJECTED)
                ->with(['cities', 'subService'])->firstOrFail();
        }

        return compact('services', 'subServices', 'regions', 'price');
    }

    public function createPrice(ProviderProfile $profile, array $input): SubServicePrice
    {
        return $this->saveProviderPrice($profile, $input);
    }

    public function resubmitPrice(ProviderProfile $profile, int $priceId, array $input): SubServicePrice
    {
        return $this->saveProviderPrice($profile, $input, $priceId);
    }

    public function orders(ProviderProfile $profile): array
    {
        $visibleStatuses = BookingStatus::query()
            ->where('show_in_provider_portal', true)
            ->with('translations')
            ->orderBy('id')
            ->get();

        $selectedStatus = request()->query('status');
        $query = $this->ordersQuery($profile);
        if ($selectedStatus && $visibleStatuses->contains('code', $selectedStatus)) {
            $query->where('booking_statuses.code', $selectedStatus);
        }

        return [
            'orders' => $query->latest('bookings.created_at')->paginate(20)->withQueryString(),
            'visibleStatuses' => $visibleStatuses,
            'selectedStatus' => $selectedStatus,
        ];
    }

    public function showOrder(ProviderProfile $profile, int $bookingId): array
    {
        $booking = $this->providerOrders($profile)->with([
            'resident', 'residentHome', 'home.city.translations', 'service.translations', 'subService.translations',
            'status.translations', 'assignment.providerContract', 'bookingMedia',
            'bookingStatusHistory' => fn ($q) => $q->whereHas('status', fn (Builder $sq) => $sq->where('show_in_provider_portal', true))->with('status.translations'),
        ])->whereKey($bookingId)->firstOrFail();

        $availableNextStatuses = $this->visibleNextStatuses($booking->status->code);

        return compact('booking', 'availableNextStatuses');
    }

    public function orderForm(ProviderProfile $profile): array
    {
        $memberships = ResidentHome::query()
            ->join('users', 'resident_homes.user_id', '=', 'users.id')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->join('homes', 'resident_homes.home_id', '=', 'homes.id')
            ->join('cities', 'homes.city_id', '=', 'cities.id')
            ->leftJoin('regions', 'cities.region_id', '=', 'regions.id')
            ->where('roles.code', RoleCode::Resident->value)
            ->select(
                'resident_homes.id',
                'resident_homes.unit_label',
                'users.name as resident_name',
                'homes.name as home_name',
                'homes.city_id',
                'regions.code as region_code',
            )
            ->orderBy('users.name')->get();

        $serviceIds = $profile->services()->where('services.is_active', true)->select('services.id');
        $services = $profile->services()->where('services.is_active', true)
            ->with('translations')->orderBy('services.code')->get();
        $subServices = SubService::query()->whereIn('service_id', $serviceIds)
            ->where('is_active', true)->with('translations')
            ->orderBy('sort_order')->orderBy('id')->get();
        $pricesByCity = [];
        foreach ($memberships->unique('city_id') as $membership) {
            foreach ($subServices as $subService) {
                $catalogPrice = $membership->region_code === 'eastern'
                    ? $subService->price_eastern
                    : $subService->price_qassim_madinah_kharj;
                if ($catalogPrice !== null) {
                    $pricesByCity[$membership->city_id][$subService->id] = (float) $catalogPrice;
                }
            }
        }
        if ($subServices->isNotEmpty()) {
            $prices = SubServicePrice::query()
                ->where('provider_profile_id', $profile->id)
                ->whereIn('sub_service_id', $subServices->modelKeys())
                ->where('is_active', true)
                ->where('approval_status', SubServicePrice::APPROVAL_APPROVED)
                ->with('cities:id')->get();
            foreach ($prices as $price) {
                foreach ($price->cities as $city) {
                    $pricesByCity[$city->id][$price->sub_service_id] = (float) $price->price;
                }
            }
        }

        return compact('memberships', 'services', 'subServices', 'pricesByCity');
    }

    public function createOrder(ProviderProfile $profile, User $actor, array $input): Booking
    {
        return DB::transaction(function () use ($profile, $actor, $input) {
            $membership = ResidentHome::query()->whereKey($input['resident_home_id'])
                ->whereHas('user.role', fn (Builder $query) => $query->where('code', RoleCode::Resident->value))
                ->lockForUpdate()->first();
            if (!$membership) {
                throw ValidationException::withMessages(['resident_home_id' => __('Select a valid resident unit.')]);
            }

            $service = $profile->services()->where('services.id', $input['service_id'])
                ->where('services.is_active', true)->lockForUpdate()->first();
            if (!$service) {
                throw ValidationException::withMessages(['service_id' => __('Choose an active service offered by your company.')]);
            }

            $subService = null;
            $subServicePrice = null;
            $catalogPrice = null;
            if (!empty($input['sub_service_id'])) {
                $subService = SubService::query()->whereKey($input['sub_service_id'])
                    ->where('service_id', $service->id)->where('is_active', true)->first();
                if (!$subService) {
                    throw ValidationException::withMessages(['sub_service_id' => __('Choose an active sub service for the selected service.')]);
                }

                $city = Home::query()->with('city.region')->whereKey($membership->home_id)->firstOrFail()->city;
                $subServicePrice = SubServicePrice::query()
                    ->where('provider_profile_id', $profile->id)
                    ->where('sub_service_id', $subService->id)
                    ->where('is_active', true)
                    ->where('approval_status', SubServicePrice::APPROVAL_APPROVED)
                    ->whereHas('cities', fn (Builder $query) => $query->where('cities.id', $city->id))
                    ->lockForUpdate()->first();
                $catalogPrice = $city->region?->code === 'eastern'
                    ? $subService->price_eastern
                    : $subService->price_qassim_madinah_kharj;
            } elseif (SubService::query()->where('service_id', $service->id)->where('is_active', true)->exists()) {
                throw ValidationException::withMessages(['sub_service_id' => __('Choose a sub service.')]);
            }

            $contract = $this->activeContract($profile);
            if (!$contract) {
                throw ValidationException::withMessages(['service_id' => __('An active provider contract is required to create service orders.')]);
            }

            $pending = BookingStatus::query()->where('code', BookingStatusCode::Pending->value)->firstOrFail();
            $booking = Booking::query()->create([
                'resident_id' => $membership->user_id,
                'resident_home_id' => $membership->id,
                'home_id' => $membership->home_id,
                'service_id' => $service->id,
                'sub_service_id' => $subService?->id,
                'sub_service_price_id' => $subServicePrice?->id,
                'provider_id' => $profile->user_id,
                'service_provider_id' => $profile->id,
                'booking_status_id' => $pending->id,
                'scheduled_at' => $input['scheduled_at'],
                'total_amount' => $subServicePrice?->price ?? $catalogPrice ?? $service->base_price,
                'notes' => $input['notes'] ?? null,
            ]);

            BookingProviderAssignment::query()->create([
                'booking_id' => $booking->id,
                'provider_profile_id' => $profile->id,
                'provider_contract_id' => $contract->id,
                ...AdminActor::attributes($actor, 'assigned_by'),
                'assigned_at' => now(),
                'provider_note' => $input['notes'] ?? null,
            ]);

            BookingStatusHistory::query()->create([
                'booking_id' => $booking->id,
                'booking_status_id' => $pending->id,
                ...AdminActor::attributes($actor, 'changed_by'),
                'note' => __('Created by service provider.'),
                'created_at' => now(),
            ]);

            DB::afterCommit(fn () => event(new \App\Modules\Bookings\Events\BookingsChanged(BookingOperation::AdminOrderCreationStoreService, $actor->id)));

            return $booking;
        });
    }

    public function transitionOrder(ProviderProfile $profile, User $actor, int $bookingId, array $input): string
    {
        $booking = $this->providerOrders($profile)->with('status')->whereKey($bookingId)->firstOrFail();
        $from = $booking->status->code;
        $to = $input['status'];
        $allowed = $this->visibleNextStatuses($from)->pluck('code')->all();

        if (!in_array($to, $allowed, true)) {
            throw ValidationException::withMessages(['status' => __('This order cannot be moved to the selected status.')]);
        }

        $statusId = BookingStatus::query()
            ->where('code', $to)
            ->where('show_in_provider_portal', true)
            ->value('id');
        if (!$statusId) {
            throw ValidationException::withMessages(['status' => __('The requested order status is not configured.')]);
        }

        return $this->orderAdmin->update([
            'booking_status_id' => $statusId,
            'provider_profile_id' => $profile->id,
            'gross_amount' => $input['gross_amount'] ?? null,
            'note' => $input['note'] ?? null,
        ], $actor, $bookingId);
    }

    /**
     * إرجاع الحالات التالية المتاحة والظاهرة لمزود الخدمة حسب ترتيب دورة حياة الطلب.
     */
    public function visibleNextStatuses(string $currentCode): \Illuminate\Support\Collection
    {
        $sequence = [
            BookingStatusCode::Pending->value,
            BookingStatusCode::Confirmed->value,
            BookingStatusCode::InProgress->value,
            BookingStatusCode::Completed->value,
        ];

        $currentIndex = array_search($currentCode, $sequence, true);
        if ($currentIndex === false || $currentIndex >= count($sequence) - 1) {
            return collect();
        }

        $nextCodes = array_slice($sequence, $currentIndex + 1);

        $statusesByCode = BookingStatus::query()
            ->whereIn('code', $nextCodes)
            ->where('show_in_provider_portal', true)
            ->with('translations')
            ->get()
            ->keyBy('code');

        return collect($nextCodes)
            ->map(fn (string $code) => $statusesByCode->get($code))
            ->filter()
            ->values();
    }

    public function prices(ProviderProfile $profile): array
    {
        $prices = $profile->prices()->with([
            'subService.translations',
            'subService.service.translations',
            'region.translations',
            'cities.translations',
        ])->latest('id')->paginate(25)->withQueryString();

        return compact('prices');
    }

    private function saveProviderPrice(ProviderProfile $profile, array $input, ?int $priceId = null): SubServicePrice
    {
        return DB::transaction(function () use ($profile, $input, $priceId) {
            ProviderProfile::query()->whereKey($profile->id)->lockForUpdate()->firstOrFail();

            if (!$profile->services()->whereKey($input['service_id'])->where('services.is_active', true)->exists()) {
                throw ValidationException::withMessages(['service_id' => __('Choose an active service offered by your company.')]);
            }

            $subService = SubService::query()->whereKey($input['sub_service_id'])
                ->where('service_id', $input['service_id'])->where('is_active', true)->first();
            if (!$subService) {
                throw ValidationException::withMessages(['sub_service_id' => __('Choose an active sub service for the selected service.')]);
            }

            $region = Region::query()->available()->whereKey($input['region_id'])->first();
            if (!$region) {
                throw ValidationException::withMessages(['region_id' => __('Choose an active region.')]);
            }

            $cityIds = array_values(array_unique(array_map('intval', $input['city_ids'])));
            $validCityCount = City::query()->available()
                ->where('region_id', $region->id)->whereIn('id', $cityIds)->count();
            if ($validCityCount !== count($cityIds)) {
                throw ValidationException::withMessages(['city_ids' => __('Each selected city must belong to the selected region.')]);
            }

            $price = $priceId === null
                ? null
                : $profile->prices()->whereKey($priceId)->lockForUpdate()->firstOrFail();
            if ($price && $price->approval_status !== SubServicePrice::APPROVAL_REJECTED) {
                throw ValidationException::withMessages(['price' => __('Only rejected prices can be resubmitted.')]);
            }

            $conflict = SubServicePrice::query()
                ->when($price, fn ($query) => $query->where('id', '!=', $price->id))
                ->where('provider_profile_id', $profile->id)
                ->where('sub_service_id', $subService->id)
                ->whereHas('cities', fn ($query) => $query->whereIn('cities.id', $cityIds))
                ->lockForUpdate()->first();
            if ($conflict) {
                throw ValidationException::withMessages([
                    'city_ids' => [__('A price already exists for this service and one or more selected cities. Edit the existing price instead.')],
                ]);
            }

            $values = [
                'sub_service_id' => $subService->id,
                'region_id' => $region->id,
                'price' => $input['price'],
                'is_active' => true,
                'approval_status' => SubServicePrice::APPROVAL_PENDING,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'rejection_reason' => null,
            ];
            if ($price) {
                $price->update($values);
            } else {
                $price = $profile->prices()->create($values);
            }
            $price->cities()->sync($cityIds);

            return $price;
        });
    }

    private function emergencyAssignments(ProviderProfile $profile): Builder
    {
        return EmergencyProviderAssignment::query()
            ->where('provider_profile_id', $profile->id)
            ->whereHas('emergencyRequest.status', fn (Builder $query) => $query->where('show_in_provider_portal', true))
            ->with([
                'emergencyRequest.status.translations',
                'emergencyRequest.emergencyType.translations',
                'emergencyRequest.home.city.translations',
            ])->select('emergency_provider_assignments.*');
    }

    public function team(ProviderProfile $profile): array
    {
        $members = User::query()->with('role')->where('provider_profile_id', $profile->id)
            ->whereHas('role', fn (Builder $query) => $query->where('code', RoleCode::Provider->value))
            ->latest('id')->paginate(20)->withQueryString();

        return [
            'members' => $members,
            'permissions' => self::PERMISSIONS,
            'ownerId' => (int) $profile->user_id,
        ];
    }

    public function teamMember(ProviderProfile $profile, int $memberId): array
    {
        $member = User::query()->where('provider_profile_id', $profile->id)
            ->whereHas('role', fn (Builder $query) => $query->where('code', RoleCode::Provider->value))
            ->whereKey($memberId)->firstOrFail();
        abort_if((int) $member->id === (int) $profile->user_id, 403);

        return ['member' => $member, 'permissions' => self::PERMISSIONS];
    }

    public function saveTeamMember(ProviderProfile $profile, User $actor, array $input, ?int $memberId = null): int
    {
        return DB::transaction(function () use ($profile, $actor, $input, $memberId) {
            $permissions = array_values(array_unique(array_intersect($input['permissions'] ?? [], self::PERMISSIONS)));
            if (!$this->isOwner($actor, $profile) && array_diff($permissions, $actor->permissions ?? [])) {
                throw ValidationException::withMessages(['permissions' => __('Cannot grant permissions you do not have.')]);
            }

            if ($memberId === null) {
                $input['provider_profile_id'] = $profile->id;
                $input['permissions'] = $permissions;
                return $this->accounts->create($input, $actor->id)->id;
            }

            $member = User::query()->where('provider_profile_id', $profile->id)
                ->whereHas('role', fn (Builder $query) => $query->where('code', RoleCode::Provider->value))
                ->whereKey($memberId)->lockForUpdate()->firstOrFail();
            abort_if((int) $member->id === (int) $profile->user_id, 403);
            if ((int) $member->id === (int) $actor->id) {
                throw ValidationException::withMessages(['permissions' => __('Cannot change your own provider permissions.')]);
            }
            $member->update([
                'name' => $input['name'],
                'email' => $input['email'] ?? null,
                'phone' => $input['phone'],
                'locale' => $input['locale'],
                'permissions' => $permissions,
                ...(!empty($input['password']) ? ['password' => $input['password']] : []),
            ]);

            return $memberId;
        });
    }

    public function removeTeamMember(ProviderProfile $profile, int $memberId): void
    {
        $member = User::query()->where('provider_profile_id', $profile->id)
            ->whereHas('role', fn (Builder $query) => $query->where('code', RoleCode::Provider->value))
            ->whereKey($memberId)->firstOrFail();
        abort_if((int) $member->id === (int) $profile->user_id, 403);
        $member->update(['provider_profile_id' => null, 'permissions' => []]);
    }

    private function providerOrders(ProviderProfile $profile): Builder
    {
        return Booking::query()
            ->whereHas('assignment', fn (Builder $query) => $query->where('provider_profile_id', $profile->id))
            ->whereHas('status', fn (Builder $query) => $query->where('show_in_provider_portal', true));
    }

    private function ordersQuery(ProviderProfile $profile): Builder
    {
        $locale = app()->getLocale();

        return Booking::query()
            ->join('resident_homes', 'bookings.resident_home_id', '=', 'resident_homes.id')
            ->join('users as residents', 'bookings.resident_id', '=', 'residents.id')
            ->join('homes', 'bookings.home_id', '=', 'homes.id')
            ->join('services', 'bookings.service_id', '=', 'services.id')
            ->leftJoin('service_translations', function ($join) use ($locale) {
                $join->on('services.id', '=', 'service_translations.service_id')
                    ->where('service_translations.locale', $locale);
            })
            ->join('booking_statuses', function ($join) {
                $join->on('bookings.booking_status_id', '=', 'booking_statuses.id')
                    ->where('booking_statuses.show_in_provider_portal', true);
            })
            ->leftJoin('booking_status_translations', function ($join) use ($locale) {
                $join->on('booking_statuses.id', '=', 'booking_status_translations.booking_status_id')
                    ->where('booking_status_translations.locale', $locale);
            })
            ->join('booking_provider_assignments', 'bookings.id', '=', 'booking_provider_assignments.booking_id')
            ->where('booking_provider_assignments.provider_profile_id', $profile->id)
            ->select(
                'bookings.*', 'residents.name as resident_name', 'homes.name as home_name',
                'resident_homes.unit_label', 'services.code as service_code',
                'service_translations.name as service_name', 'booking_statuses.code as status_code',
                'booking_status_translations.name as status_name'
            );
    }

    private function activeContract(ProviderProfile $profile): ?ProviderContract
    {
        return ProviderContract::query()
            ->join('provider_contract_statuses', 'provider_contracts.status_id', '=', 'provider_contract_statuses.id')
            ->where('provider_contracts.provider_profile_id', $profile->id)
            ->where('provider_contract_statuses.code', ContractStatusCode::Active->value)
            ->whereDate('provider_contracts.starts_on', '<=', today())
            ->where(fn (Builder $query) => $query->whereNull('provider_contracts.ends_on')->orWhereDate('provider_contracts.ends_on', '>=', today()))
            ->select('provider_contracts.*')->latest('provider_contracts.starts_on')->first();
    }
}
