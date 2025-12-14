<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class GuruDashboard extends Controller
{
  public function index()
  {
    $user = Auth::user();

    // Data dummy perkembangan anak dari 5 kategori penilaian
    $categories = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $series = [[
      'name' => 'Perkembangan Anak',
      'data' => [2, 3, 2, 4, 3, 2, 1, 3, 4, 2, 3, 4] // Nilai 0-4 untuk 12 bulan
    ]];

    $dashboardData = [
      'role' => 'guru',
      'message' => 'Selamat datang di Dashboard Guru',
      'chartData' => [
        'title' => 'Perkembangan Anak Didik per Bulan (0-4)',
        'categories' => $categories,
        'series' => $series
      ]
    ];

    return view('content.dashboard.guru-dashboard', compact('dashboardData', 'user'));
  }
}
