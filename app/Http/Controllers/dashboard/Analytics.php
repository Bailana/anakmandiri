<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AnakDidik;
use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Analytics extends Controller
{
  public function index()
  {
    $user = Auth::user();
    $role = $user->role;

    // Data yang berbeda-beda untuk setiap role
    $dashboardData = match ($role) {
      'admin' => $this->getAdminDashboard(),
      'guru' => $this->getGuruDashboard(),
      'konsultan' => $this->getKonsultanDashboard(),
      'terapis' => $this->getTerapisDashboard(),
      default => []
    };

    // Pilih view berdasarkan role
    $view = match ($role) {
      'admin' => 'content.dashboard.admin-dashboard',
      'guru' => 'content.dashboard.guru-dashboard',
      'konsultan' => 'content.dashboard.konsultan-dashboard',
      'terapis' => 'content.dashboard.terapis-dashboard',
      default => 'content.dashboard.dashboards-analytics'
    };

    return view($view, [
      'role' => $role,
      'dashboardData' => $dashboardData,
      'user' => $user,
    ]);
  }

  private function getAdminDashboard()
  {
    $totalUsers = User::count();
    $usersByRole = User::selectRaw('role, COUNT(*) as count')
      ->groupBy('role')
      ->get()
      ->pluck('count', 'role')
      ->toArray();

    // Total active anak didik (those with an active therapy program)
    $totalActiveAnakDidik = AnakDidik::whereHas('therapyPrograms', function ($q) {
      $q->where('is_active', true);
    })->count();

    // Data anak didik per bulan dalam 1 tahun
    $currentYear = date('Y');
    $anakDidikPerMonth = AnakDidik::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
      ->whereYear('created_at', $currentYear)
      ->groupBy('month')
      ->orderBy('month')
      ->pluck('count', 'month')
      ->toArray();

    // Format data untuk 12 bulan
    $monthlyData = [];
    $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
    for ($i = 1; $i <= 12; $i++) {
      $monthlyData[] = $anakDidikPerMonth[$i] ?? 0;
    }

    return [
      'activities' => Activity::with('user')
        ->whereDate('created_at', Carbon::today())
        ->orderBy('created_at', 'desc')
        ->paginate(10),
      'title' => 'Admin Dashboard',
      'totalUsers' => $totalUsers,
      'usersByRole' => $usersByRole,
      'chartData' => [
        'categories' => ['Admin', 'Guru', 'Konsultan', 'Terapis'],
        'series' => [
          [
            'name' => 'Jumlah Pengguna',
            'data' => [
              $usersByRole['admin'] ?? 0,
              $usersByRole['guru'] ?? 0,
              $usersByRole['konsultan'] ?? 0,
              $usersByRole['terapis'] ?? 0,
            ]
          ]
        ]
      ],
      'lineChartData' => [
        'categories' => $monthNames,
        'series' => [
          [
            'name' => 'Anak Didik Masuk',
            'data' => $monthlyData
          ]
        ],
        'title' => 'Data Anak Didik Masuk per Bulan (' . $currentYear . ')'
      ],
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
          'value' => $usersByRole['guru'] ?? 0,
          'color' => 'success',
          'icon' => 'ri-book-line'
        ],
        [
          'label' => 'Konsultan',
          'value' => $usersByRole['konsultan'] ?? 0,
          'color' => 'warning',
          'icon' => 'ri-lightbulb-line'
        ],
        [
          'label' => 'Terapis',
          'value' => $usersByRole['terapis'] ?? 0,
          'color' => 'info',
          'icon' => 'ri-heart-line'
        ],
      ]
    ];
  }

  private function getGuruDashboard()
  {
    return [
      'title' => 'Guru Dashboard',
      'stats' => [
        [
          'label' => 'Total Anak Didik',
          'value' => 24,
          'color' => 'primary',
          'icon' => 'ri-team-line'
        ],
        [
          'label' => 'Kelas Hari Ini',
          'value' => 3,
          'color' => 'success',
          'icon' => 'ri-calendar-event-line'
        ],
        [
          'label' => 'Tugas Pending',
          'value' => 5,
          'color' => 'warning',
          'icon' => 'ri-checkbox-line'
        ],
        [
          'label' => 'Rating Guru',
          'value' => '4.8/5',
          'color' => 'info',
          'icon' => 'ri-star-line'
        ],
      ],
      'chartData' => [
        'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        'series' => [
          [
            'name' => 'Jumlah Siswa',
            'data' => [18, 20, 22, 24, 25, 24]
          ]
        ],
        'title' => 'Performa Mengajar (6 Bulan Terakhir)'
      ]
    ];
  }

  private function getKonsultanDashboard()
  {
    return [
      'title' => 'Konsultan Dashboard',
      'stats' => [
        [
          'label' => 'Total Konsultasi',
          'value' => 42,
          'color' => 'primary',
          'icon' => 'ri-chat-3-line'
        ],
        [
          'label' => 'Konsultasi Hari Ini',
          'value' => 4,
          'color' => 'success',
          'icon' => 'ri-calendar-line'
        ],
        [
          'label' => 'Pending Respon',
          'value' => 3,
          'color' => 'warning',
          'icon' => 'ri-mail-line'
        ],
        [
          'label' => 'Kepuasan Klien',
          'value' => '4.7/5',
          'color' => 'info',
          'icon' => 'ri-thumb-up-line'
        ],
      ],
      'chartData' => [
        'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        'series' => [
          [
            'name' => 'Jumlah Konsultasi',
            'data' => [12, 14, 16, 18, 15, 20]
          ]
        ],
        'title' => 'Jumlah Konsultasi (6 Bulan Terakhir)'
      ]
    ];
  }

  private function getTerapisDashboard()
  {
    return [
      'title' => 'Terapis Dashboard',
      'stats' => [
        [
          'label' => 'Total Pasien',
          'value' => 18,
          'color' => 'primary',
          'icon' => 'ri-user-heart-line'
        ],
        [
          'label' => 'Sesi Hari Ini',
          'value' => 2,
          'color' => 'success',
          'icon' => 'ri-time-line'
        ],
        [
          'label' => 'Progres Baik',
          'value' => 14,
          'color' => 'info',
          'icon' => 'ri-trending-up-line'
        ],
        [
          'label' => 'Follow-up Needed',
          'value' => 2,
          'color' => 'warning',
          'icon' => 'ri-alert-line'
        ],
      ],
      'chartData' => [
        'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        'series' => [
          [
            'name' => 'Jumlah Sesi',
            'data' => [8, 10, 12, 14, 13, 15]
          ]
        ],
        'title' => 'Jumlah Sesi Terapi (6 Bulan Terakhir)'
      ]
    ];
  }
}
