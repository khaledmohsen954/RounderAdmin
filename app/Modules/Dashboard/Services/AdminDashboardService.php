<?php

namespace App\Modules\Dashboard\Services;
use App\Modules\Bookings\Enums\BookingStatusCode;
use App\Modules\Identity\Enums\RoleCode;

use App\Modules\Community\Models\CommunityPost;
use App\Modules\Emergencies\Models\EmergencyRequest;
use App\Modules\Facilities\Models\FacilityReport;
use App\Modules\Community\Models\Initiative;
use App\Modules\Providers\Models\ProviderCommissionEntry;
use App\Modules\Properties\Models\ResidentHome;
use App\Modules\Catalog\Models\ResidentSubscription;
use App\Modules\Catalog\Models\ServicePackage;
use App\Modules\Bookings\Models\Booking;
use App\Modules\Finance\Models\Payment;
use App\Modules\Providers\Models\ProviderProfile;
use App\Modules\Identity\Models\User;

/** قواعد عمل Dashboard؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class AdminDashboardService
{

    /**
     * قراءة بيانات المجال مع الحفاظ على قيود الوصول.
     * @param array $input
     * @param User|\App\Modules\Identity\Models\Admin|null $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __invoke(array $input, User|\App\Modules\Identity\Models\Admin|null $actor): mixed
    {
        // المؤشرات محسوبة من السجلات الفعلية؛ العمولة المعكوسة لا تدخل في الإجمالي.
        $stats = [
            'residents' => User::whereHas('role', fn ($query) => $query->where('code', RoleCode::Resident->value))->count(),
            'providers' => ProviderProfile::where('is_approved', true)->count(),
            'bookings' => Booking::count(),
            'pending_bookings' => Booking::whereHas('status', fn ($query) => $query->where('code', BookingStatusCode::Pending->value))->count(),
            'revenue' => Payment::whereHas('status', fn ($query) => $query->where('code', \App\Modules\Finance\Enums\PaymentStatusCode::Paid->value))->sum('amount'),
            'linked_homes' => ResidentHome::query()->distinct('home_id')->count('home_id'),
            'open_reports' => FacilityReport::query()->whereNull('resolved_at')->count(),
            'community_posts' => CommunityPost::query()->where('is_hidden', false)->count(),
            'active_initiatives' => Initiative::query()->where('is_published', true)->where('ends_at', '>=', now())->count(),
            'pending_emergencies' => EmergencyRequest::query()
                ->whereHas('status', fn ($query) => $query->whereNotIn('code', [\App\Modules\Emergencies\Enums\EmergencyStatusCode::Resolved->value, \App\Modules\Emergencies\Enums\EmergencyStatusCode::Cancelled->value]))->count(),
            'active_subscriptions' => ResidentSubscription::query()
                ->whereHas('status', fn ($query) => $query->where('code', \App\Modules\Catalog\Enums\SubscriptionStatusCode::Active->value))->where('resident_subscriptions.ends_at', '>', now())->count(),
            'commission_total' => ProviderCommissionEntry::query()
                ->whereHas('status', fn ($query) => $query->where('code', '!=', \App\Modules\Providers\Enums\CommissionStatusCode::Reversed->value))->sum('commission_amount'),
            'available_packages' => ServicePackage::query()->where('is_active', true)->count(),
        ];

        $recent = Booking::with(['resident', 'service.translations', 'status.translations'])->latest()->limit(8)->get();
        return compact('stats', 'recent');
    }
}
