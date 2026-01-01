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
          'label' => 'Total Pengguna',
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


    // Anak Didik registration per month for all years
    $monthlyCounts = AnakDidik::selectRaw('YEAR(tanggal_pendaftaran) as year, MONTH(tanggal_pendaftaran) as month, COUNT(*) as count')
      ->groupBy('year', 'month')
      ->orderBy('year')
      ->orderBy('month')
      ->get();

    $months = [
      1 => 'Jan',
      2 => 'Feb',
      3 => 'Mar',
      4 => 'Apr',
      5 => 'Mei',
      6 => 'Jun',
      7 => 'Jul',
      8 => 'Agu',
      9 => 'Sep',
      10 => 'Okt',
      11 => 'Nov',
      12 => 'Des'
    ];
    $categories = array_values($months);

    // Get all years present in the data
    $years = $monthlyCounts->pluck('year')->unique()->sort()->values();
    $series = [];
    foreach ($years as $year) {
      $data = [];
      for ($i = 1; $i <= 12; $i++) {
        $count = $monthlyCounts->first(function ($item) use ($year, $i) {
          return $item->year == $year && $item->month == $i;
        });
        $data[] = $count ? $count->count : 0;
      }
      $series[] = [
        'name' => (string)$year,
        'data' => $data
      ];
    }


    // Calculate total Anak Didik registered per month (all years combined)
    $totalPerMonth = [];
    for ($i = 1; $i <= 12; $i++) {
      $totalPerMonth[$i] = $monthlyCounts->where('month', $i)->sum('count');
    }

    // Add total as a new series (first in the list)
    array_unshift($series, [
      'name' => 'Total per Bulan',
      'data' => array_values($totalPerMonth)
    ]);

    $dashboardData['lineChartData'] = [
      'title' => 'Pendaftaran Anak Didik Tiap Bulan',
      'series' => $series,
      'categories' => $categories
    ];

    return view('content.dashboard.admin-dashboard', compact('dashboardData', 'user'));
  }
}
