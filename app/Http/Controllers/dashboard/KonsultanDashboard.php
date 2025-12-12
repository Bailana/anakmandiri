<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class KonsultanDashboard extends Controller
{
  public function index()
  {
    $user = Auth::user();

    $dashboardData = [
      'role' => 'konsultan',
      'message' => 'Selamat datang di Dashboard Konsultan',
    ];

    return view('content.dashboard.konsultan-dashboard', compact('dashboardData', 'user'));
  }
}
