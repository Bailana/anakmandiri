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

    // Hitung jumlah anak didik per jenis terapi untuk terapis ini (aktif)
    $therapyCounts = [];
    $assignments = \App\Models\GuruAnakDidik::where('user_id', $user->id)
      ->where('status', 'aktif')
      ->with('anakDidik')
      ->get();

    foreach ($assignments as $assign) {
      $anakId = $assign->anak_didik_id;
      $jenisRaw = $assign->jenis_terapi ?? '';
      $parts = array_filter(array_map('trim', explode('|', $jenisRaw)));
      if (empty($parts)) {
        continue;
      }
      foreach ($parts as $p) {
        if (!isset($therapyCounts[$p])) $therapyCounts[$p] = [];
        // collect unique anak ids per therapy
        $therapyCounts[$p][$anakId] = true;
      }
    }

    // Transform to label/count and sort desc
    $therapyCards = collect($therapyCounts)->map(function ($ids, $label) {
      return ['label' => $label, 'count' => count($ids)];
    })->sortByDesc('count')->values()->take(3)->toArray();

    $dashboardData['therapyCounts'] = $therapyCards;

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

    // sort desc
    arsort($hoursByTerapis);
    $terapisLabels = array_keys($hoursByTerapis);
    $terapisSeries = array_values($hoursByTerapis);

    $dashboardData['terapisHoursChart'] = [
      'title' => 'Jam Terapi per Terapis',
      'labels' => $terapisLabels,
      'series' => $terapisSeries,
    ];

    return view('content.dashboard.terapis-dashboard', compact('dashboardData', 'user'));
  }
}
