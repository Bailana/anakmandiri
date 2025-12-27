<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TerapisDashboard extends Controller
{
  public function index()
  {
    $user = Auth::user();

    // Ambil jadwal terapi hari ini untuk terapis ini
    $today = now()->format('Y-m-d');
    $jadwalHariIni = \App\Models\GuruAnakDidikSchedule::with(['assignment.anakDidik'])
      ->whereHas('assignment', function ($q) use ($user) {
        $q->where('user_id', $user->id);
      })
      ->whereDate('tanggal_mulai', $today)
      ->orderBy('jam_mulai')
      ->get();

    $dashboardData = [
      'role' => 'terapis',
      'message' => 'Selamat datang di Dashboard Terapis',
      'jadwal_hari_ini' => $jadwalHariIni,
    ];

    return view('content.dashboard.terapis-dashboard', compact('dashboardData', 'user'));
  }
}
