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

    // Total active anak didik (status = 'aktif')
    $totalActiveAnakDidik = AnakDidik::where('status', 'aktif')->count();

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
      'stats' => [
        [
          'label' => 'Anak Didik Aktif',
          'value' => $totalActiveAnakDidik,
          'color' => 'success',
          'icon' => 'ri-group-line'
        ],
        [
          'label' => 'Total Users',
          'value' => $totalUsers,
          'color' => 'primary',
          'icon' => 'ri-user-line'
        ],
        [
          'label' => 'Guru',
          'value' => $totalGuru,
          'color' => 'success',
          'icon' => 'ri-book-line'
        ],
        [
          'label' => 'Konsultan',
          'value' => $totalKonsultan,
          'color' => 'warning',
          'icon' => 'ri-lightbulb-line'
        ],
        [
          'label' => 'Terapis',
          'value' => $totalTerapis,
          'color' => 'info',
          'icon' => 'ri-heart-line'
        ],
      ],
    ];

    return view('content.dashboard.admin-dashboard', compact('dashboardData', 'user'));
  }
}
