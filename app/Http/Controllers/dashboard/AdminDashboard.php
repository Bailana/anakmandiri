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

    // Ambil jadwal terapi hari ini (untuk card Sesi Hari Ini)
    $today = Carbon::today()->toDateString();
    $jadwalHariIni = \App\Models\GuruAnakDidikSchedule::with(['assignment.anakDidik'])
      ->whereDate('tanggal_mulai', $today)
      ->orderBy('jam_mulai')
      ->get();

    // Hitung jam per terapis (1 jam per jadwal/sesi)
    $schedules = \App\Models\GuruAnakDidikSchedule::with('assignment')->whereHas('assignment', function ($q) {
      $q->where('status', 'aktif');
    })->get();
    $hoursByTerapis = [];
    foreach ($schedules as $s) {
      $name = trim($s->terapis_nama ?? $s->assignment->terapis_nama ?? '');
      if ($name === '') continue;
      if (!isset($hoursByTerapis[$name])) $hoursByTerapis[$name] = 0;
      $hoursByTerapis[$name] += 1; // 1 jam per schedule
    }

    arsort($hoursByTerapis);
    $terapisLabels = array_keys($hoursByTerapis);
    $terapisSeries = array_values($hoursByTerapis);

    // Masukkan data tambahan ke dashboardData
    $dashboardData['jadwal_hari_ini'] = $jadwalHariIni;
    $dashboardData['terapisHoursChart'] = [
      'title' => 'Jam Terapi per Terapis',
      'labels' => $terapisLabels,
      'series' => $terapisSeries,
    ];


    // Anak Didik registration per year (hitung jumlah pendaftaran per tahun)
    $yearlyCounts = AnakDidik::selectRaw('YEAR(tanggal_pendaftaran) as year, COUNT(*) as count')
      ->groupBy('year')
      ->orderBy('year')
      ->get();

    $categories = $yearlyCounts->pluck('year')->map(function ($y) {
      return (string) $y;
    })->toArray();

    $dataPerYear = $yearlyCounts->pluck('count')->toArray();

    $dashboardData['lineChartData'] = [
      'title' => 'Pendaftaran Anak Didik Tahunan',
      'series' => [
        [
          'name' => 'Jumlah Anak Didik',
          'data' => $dataPerYear
        ]
      ],
      'categories' => $categories
    ];

    return view('content.dashboard.admin-dashboard', compact('dashboardData', 'user'));
  }
}
