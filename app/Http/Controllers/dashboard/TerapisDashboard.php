<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TerapisDashboard extends Controller
{
  public function index()
  {
    $user = Auth::user();

    $dashboardData = [
      'role' => 'terapis',
      'message' => 'Selamat datang di Dashboard Terapis',
    ];

    return view('content.dashboard.terapis-dashboard', compact('dashboardData', 'user'));
  }
}
