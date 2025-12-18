<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Activity;
use App\Models\AnakDidik;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AdminDashboard extends Controller
{
  public function index()
  {
    $user = Auth::user();
    $totalUsers = User::count();
    $totalGuru = User::where('role', 'guru')->count();
    $totalKonsultan = User::where('role', 'konsultan')->count();
    $totalTerapis = User::where('role', 'terapis')->count();

    // Total active anak didik (those with an active therapy program)
    $totalActiveAnakDidik = AnakDidik::whereHas('therapyPrograms', function ($q) {
      $q->where('is_active', true);
    })->count();

    // Get today's activities with pagination (10 per page)
    $activities = Activity::with('user')
      ->whereDate('created_at', Carbon::today())
      ->orderBy('created_at', 'desc')
      ->paginate(10);

    $dashboardData = [
      'total_users' => $totalUsers,
      'total_guru' => $totalGuru,
      'total_konsultan' => $totalKonsultan,
      'total_terapis' => $totalTerapis,
      'total_anak_didik_active' => $totalActiveAnakDidik,
      'activities' => $activities,
    ];

    return view('content.dashboard.admin-dashboard', compact('dashboardData', 'user'));
  }
}
