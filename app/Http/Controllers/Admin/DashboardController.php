<?php
namespace App\Http\Controllers\Admin;

use App\Models\CommunityPost;
use App\Models\EmergencyRequest;
use App\Models\FacilityReport;
use App\Models\Initiative;
use App\Models\ProviderCommissionEntry;
use App\Models\ResidentHome;
use App\Models\ResidentSubscription;
use App\Models\ServicePackage;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\ProviderProfile;
use App\Models\User;

/** مؤشرات لوحة الإدارة من السجلات الفعلية والعلاقات. */
class DashboardController extends Controller
{
    public function __invoke()
    {
        // المؤشرات محسوبة من السجلات الفعلية؛ العمولة المعكوسة لا تدخل في الإجمالي.
        $stats = [
            'residents' => User::whereHas('role', fn ($query) => $query->where('code', 'resident'))->count(),
            'providers' => ProviderProfile::where('is_approved', true)->count(),
            'bookings' => Booking::count(),
            'pending_bookings' => Booking::whereHas('status', fn ($query) => $query->where('code', 'pending'))->count(),
            'revenue' => Payment::whereHas('status', fn ($query) => $query->where('code', 'paid'))->sum('amount'),
            'linked_homes' => ResidentHome::query()->distinct('home_id')->count('home_id'),
            'open_reports' => FacilityReport::query()->whereNull('resolved_at')->count(),
            'community_posts' => CommunityPost::query()->where('is_hidden', false)->count(),
            'active_initiatives' => Initiative::query()->where('is_published', true)->where('ends_at', '>=', now())->count(),
            'pending_emergencies' => EmergencyRequest::query()
                ->whereHas('status', fn ($query) => $query->whereNotIn('code', ['resolved', 'cancelled']))->count(),
            'active_subscriptions' => ResidentSubscription::query()
                ->whereHas('status', fn ($query) => $query->where('code', 'active'))->where('resident_subscriptions.ends_at', '>', now())->count(),
            'commission_total' => ProviderCommissionEntry::query()
                ->whereHas('status', fn ($query) => $query->where('code', '!=', 'reversed'))->sum('commission_amount'),
            'available_packages' => ServicePackage::query()->where('is_active', true)->count(),
        ];

        $recent = Booking::with(['resident', 'service.translations', 'status.translations'])->latest()->limit(8)->get();
        return view('admin.dashboard', compact('stats', 'recent'));
    }
}
