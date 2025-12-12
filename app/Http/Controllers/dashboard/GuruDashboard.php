<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class GuruDashboard extends Controller
{
  public function index()
  {
    $user = Auth::user();

    $dashboardData = [
      'role' => 'guru',
      'message' => 'Selamat datang di Dashboard Guru',
    ];

    return view('content.dashboard.guru-dashboard', compact('dashboardData', 'user'));
  }
}
