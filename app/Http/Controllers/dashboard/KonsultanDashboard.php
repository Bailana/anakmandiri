<?php

namespace App\Http\Controllers\dashboard;

use App\Models\Assessment;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Program;
use App\Models\AnakDidik;

class KonsultanDashboard extends Controller
{
  public function index()
  {
    $user = Auth::user();

    // Ambil konsultan_id dari user login (asumsi relasi user->konsultan satu-satu)
    $konsultan = $user->konsultan ?? null;
    $konsultanId = $konsultan ? $konsultan->id : null;

    // Jumlah program konsultan
    $jumlahProgramKonsultan = $konsultanId ? \App\Models\ProgramKonsultan::where('konsultan_id', $konsultanId)->count() : 0;

    // Jumlah anak didik aktif
    $jumlahAnakDidikAktif = AnakDidik::where('status', 'aktif')->count();


    // Grafik: jumlah anak didik yang sudah diobservasi/evaluasi oleh konsultan ini
    $chartCategories = [];
    $chartSeries = [];
    if ($konsultanId) {
      // Ambil jumlah assessment per anak didik (distinct anak_didik_id)
      $assessments = Assessment::where('konsultan_id', $konsultanId)
        ->selectRaw('anak_didik_id, COUNT(*) as total')
        ->groupBy('anak_didik_id')
        ->with('anakDidik')
        ->get();
      foreach ($assessments as $a) {
        $chartCategories[] = $a->anakDidik ? $a->anakDidik->nama : 'Anak Didik';
        $chartSeries[] = $a->total;
      }
    }

    // Riwayat aktivitas konsultan (ambil 10 assessment terbaru)
    $riwayatAktivitas = [];
    if ($konsultanId) {
      $riwayatAktivitas = Assessment::where('konsultan_id', $konsultanId)
        ->orderByDesc('tanggal_assessment')
        ->with('anakDidik')
        ->limit(10)
        ->get();
    }

    // Pie chart: perbandingan anak didik sudah/belum diobservasi dalam 6 bulan terakhir
    $anakDidikAktif = AnakDidik::where('status', 'aktif')->get();
    $sixMonthsAgo = now()->subMonths(6);
    $anakDidikSudahDiobservasi = $konsultanId ? Assessment::where('konsultan_id', $konsultanId)
      ->where('tanggal_assessment', '>=', $sixMonthsAgo)
      ->distinct()->pluck('anak_didik_id')->toArray() : [];
    $jumlahSudah = count($anakDidikSudahDiobservasi);
    $jumlahBelum = $anakDidikAktif->count() - $jumlahSudah;
    $pieChartData = [
      'labels' => ['Sudah Diobservasi', 'Belum Diobservasi'],
      'series' => [$jumlahSudah, $jumlahBelum],
    ];
    $anakDidikBelumDiobservasi = $anakDidikAktif->whereNotIn('id', $anakDidikSudahDiobservasi);

    $dashboardData = [
      'role' => 'konsultan',
      'message' => 'Selamat datang di Dashboard Konsultan',
      'stats' => [
        [
          'label' => 'Anak Didik Aktif',
          'value' => $jumlahAnakDidikAktif,
          'color' => 'success',
          'icon' => 'ri-group-line',
        ],
        [
          'label' => 'Program Konsultan',
          'value' => $jumlahProgramKonsultan,
          'color' => 'info',
          'icon' => 'ri-book-2-line',
        ],
      ],
      'chartData' => [
        'title' => 'Jumlah Observasi/Evaluasi per Anak Didik',
        'categories' => $chartCategories,
        'series' => [
          [
            'name' => 'Jumlah Observasi',
            'data' => $chartSeries,
          ]
        ],
      ],
      'riwayatAktivitas' => $riwayatAktivitas,
      'anakDidikBelumDiobservasi' => $anakDidikBelumDiobservasi,
      'pieChartData' => $pieChartData,
    ];

    return view('content.dashboard.konsultan-dashboard', compact('dashboardData', 'user'));
  }
}
