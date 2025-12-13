<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

class AdminDashboard extends Controller
{
  public function index()
  {
    $user = Auth::user();
    $totalUsers = User::count();
    $totalGuru = User::where('role', 'guru')->count();
    $totalKonsultan = User::where('role', 'konsultan')->count();
    $totalTerapis = User::where('role', 'terapis')->count();

    // Get latest activities
    $activities = Activity::with('user')
      ->orderBy('created_at', 'desc')
      ->limit(10)
      ->get();

    $dashboardData = [
      'total_users' => $totalUsers,
      'total_guru' => $totalGuru,
      'total_konsultan' => $totalKonsultan,
      'total_terapis' => $totalTerapis,
      'activities' => $activities,
    ];

    return view('content.dashboard.admin-dashboard', compact('dashboardData', 'user'));
  }
}
