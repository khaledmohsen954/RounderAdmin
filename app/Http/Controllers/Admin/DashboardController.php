<?php
namespace App\\Http\\Controllers\\Admin;
use App\\Http\\Controllers\\Controller;
use App\\Models\\Booking; use App\\Models\\Payment; use App\\Models\\ProviderProfile; use App\\Models\\User;
use Illuminate\\Support\\Facades\\DB;
class DashboardController extends Controller {
 public function __invoke(){
  $stats=[
   'residents'=>User::whereHas('role',fn($q)=>$q->where('code','resident'))->count(),
   'providers'=>ProviderProfile::where('is_approved',true)->count(),
   'bookings'=>Booking::count(),
   'pending_bookings'=>Booking::whereHas('status',fn($q)=>$q->where('code','pending'))->count(),
   'revenue'=>Payment::whereHas('status',fn($q)=>$q->where('code','paid'))->sum('amount'),
  ];
  $recent=Booking::with(['resident','service.translations','status.translations'])->latest()->limit(8)->get();
  $monthly=Booking::selectRaw('MONTH(created_at) as month, COUNT(*) as total')->whereYear('created_at',now()->year)->groupBy('month')->orderBy('month')->get();
  return view('admin.dashboard',compact('stats','recent','monthly'));
 }
}
