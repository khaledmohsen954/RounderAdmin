<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $stats = [
            'residents' => User::whereHas('role', fn ($query) => $query->where('code', 'resident'))->count(),
            'providers' => ProviderProfile::where('is_approved', true)->count(),
            'bookings' => Booking::count(),
            'pending_bookings' => Booking::whereHas('status', fn ($query) => $query->where('code', 'pending'))->count(),
            'revenue' => Payment::whereHas('status', fn ($query) => $query->where('code', 'paid'))->sum('amount'),
            'linked_homes' => DB::table('resident_homes')->distinct('home_id')->count('home_id'),
            'open_reports' => DB::table('facility_reports')->whereNull('resolved_at')->count(),
            'community_posts' => DB::table('community_posts')->where('is_hidden', false)->count(),
            'active_initiatives' => DB::table('initiatives')->where('is_published', true)->where('ends_at', '>=', now())->count(),
            'pending_emergencies' => DB::table('emergency_requests')
                ->join('emergency_request_statuses', 'emergency_requests.status_id', '=', 'emergency_request_statuses.id')
                ->whereNotIn('emergency_request_statuses.code', ['resolved', 'cancelled'])->count(),
            'active_subscriptions' => DB::table('resident_subscriptions')
                ->join('subscription_statuses', 'resident_subscriptions.status_id', '=', 'subscription_statuses.id')
                ->where('subscription_statuses.code', 'active')->where('resident_subscriptions.ends_at', '>', now())->count(),
            'available_packages' => DB::table('service_packages')->where('is_active', true)->count(),
        ];

        $recent = Booking::with(['resident', 'service.translations', 'status.translations'])->latest()->limit(8)->get();
        $monthly = Booking::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->whereYear('created_at', now()->year)->groupBy('month')->orderBy('month')->get();

        return view('admin.dashboard', compact('stats', 'recent', 'monthly'));
    }
}
