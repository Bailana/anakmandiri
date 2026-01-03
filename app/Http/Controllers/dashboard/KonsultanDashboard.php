<?php

namespace App\Http\Controllers\dashboard;

use App\Models\Assessment;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Program;
use App\Models\AnakDidik;
use App\Models\ProgramPsikologi;
use App\Models\ProgramWicara;
use App\Models\ProgramSI;
use App\Models\ProgramPendidikan;

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

    // Infer spesialisasi when user has no linked Konsultan record by checking program tables
    $spec = null;
    if ($konsultan) {
      $spec = strtolower(trim($konsultan->spesialisasi ?? ''));
    } else {
      if (ProgramPsikologi::where('user_id', $user->id)->exists()) {
        $spec = 'psikologi';
      } elseif (ProgramWicara::where('user_id', $user->id)->exists()) {
        $spec = 'wicara';
      } elseif (ProgramSI::where('user_id', $user->id)->exists()) {
        $spec = 'sensori integrasi';
      } elseif (
        ProgramPendidikan::where('created_by', $user->id)->exists() ||
        ($konsultanId && ProgramPendidikan::where('konsultan_id', $konsultanId)->exists())
      ) {
        $spec = 'pendidikan';
      } else {
        $spec = '';
      }
    }


    // Grafik: jumlah anak didik yang sudah diobservasi tiap bulan (6 bulan terakhir)
    $chartCategories = [];
    $chartSeries = [];
    // prepare months (last 6 months including current)
    $months = [];
    $now = now();
    for ($i = 5; $i >= 0; $i--) {
      $dt = $now->copy()->subMonths($i);
      $months[] = $dt->format('M Y');
    }

    foreach ($months as $mIndex => $label) {
      // compute month start and end
      $dt = $now->copy()->subMonths(5 - $mIndex)->startOfMonth();
      $start = $dt->copy()->startOfDay();
      $end = $dt->copy()->endOfMonth()->endOfDay();

      // count distinct anak_didik observed in this month depending on spec
      if ($spec === 'psikologi') {
        if ($konsultanId) {
          $count = ProgramPsikologi::where('konsultan_id', $konsultanId)
            ->whereBetween('created_at', [$start, $end])
            ->distinct()->pluck('anak_didik_id')->count();
        } else {
          $count = ProgramPsikologi::where('user_id', $user->id)
            ->whereBetween('created_at', [$start, $end])
            ->distinct()->pluck('anak_didik_id')->count();
        }
      } elseif ($spec === 'wicara' || $spec === 'pendidikan') {
        // both wicara and pendidikan use ProgramWicara entries by user
        $count = ProgramWicara::where('user_id', $user->id)
          ->whereBetween('created_at', [$start, $end])
          ->distinct()->pluck('anak_didik_id')->count();
      } elseif ($spec === 'sensori integrasi' || $spec === 'sensori_integrasi' || $spec === 'si') {
        $count = ProgramSI::where('user_id', $user->id)
          ->whereBetween('created_at', [$start, $end])
          ->distinct()->pluck('anak_didik_id')->count();
      } else {
        // fallback to Assessment.tanggal_assessment
        if ($konsultanId) {
          $count = Assessment::where('konsultan_id', $konsultanId)
            ->whereBetween('tanggal_assessment', [$start, $end])
            ->distinct()->pluck('anak_didik_id')->count();
        } else {
          $count = 0;
        }
      }

      $chartCategories[] = $label;
      $chartSeries[] = $count;
    }

    // Riwayat Aktivitas Konsultan removed from dashboard (not displayed)

    // Pie chart: perbandingan anak didik sudah/belum diobservasi dalam 6 bulan terakhir
    $anakDidikAktif = AnakDidik::where('status', 'aktif')->get();
    $sixMonthsAgo = now()->subMonths(6);
    // $spec already set above; use it for determining observed anak
    if ($spec === 'psikologi') {
      if ($konsultanId) {
        $anakDidikSudahDiobservasi = ProgramPsikologi::where('konsultan_id', $konsultanId)
          ->where('created_at', '>=', $sixMonthsAgo)
          ->distinct()->pluck('anak_didik_id')->toArray();
      } else {
        $anakDidikSudahDiobservasi = ProgramPsikologi::where('user_id', $user->id)
          ->where('created_at', '>=', $sixMonthsAgo)
          ->distinct()->pluck('anak_didik_id')->toArray();
      }
    } elseif ($spec === 'wicara') {
      $anakDidikSudahDiobservasi = ProgramWicara::where('user_id', $user->id)
        ->where('created_at', '>=', $sixMonthsAgo)
        ->distinct()->pluck('anak_didik_id')->toArray();
    } elseif ($spec === 'sensori integrasi' || $spec === 'sensori_integrasi' || $spec === 'si') {
      $anakDidikSudahDiobservasi = ProgramSI::where('user_id', $user->id)
        ->where('created_at', '>=', $sixMonthsAgo)
        ->distinct()->pluck('anak_didik_id')->toArray();
    } elseif ($spec === 'pendidikan') {
      // For konsultan pendidikan, consider anak didik observed via ProgramWicara by this user
      $anakDidikSudahDiobservasi = ProgramWicara::where('user_id', $user->id)
        ->where('created_at', '>=', $sixMonthsAgo)
        ->distinct()->pluck('anak_didik_id')->toArray();
    } else {
      $anakDidikSudahDiobservasi = $konsultanId ? Assessment::where('konsultan_id', $konsultanId)
        ->where('tanggal_assessment', '>=', $sixMonthsAgo)
        ->distinct()->pluck('anak_didik_id')->toArray() : [];
    }
    $jumlahSudah = count($anakDidikSudahDiobservasi);
    $jumlahBelum = $anakDidikAktif->count() - $jumlahSudah;
    // Count anak didik that this konsultan specifically hasn't observed in last 6 months
    // Use inferred $spec or existing observed list; compute even if there's no konsultan_id
    $jumlahBelumUntukKonsultan = AnakDidik::where('status', 'aktif')
      ->whereNotIn('id', $anakDidikSudahDiobservasi)
      ->count();
    $pieChartData = [
      'labels' => ['Sudah Diobservasi', 'Belum Diobservasi'],
      'series' => [$jumlahSudah, $jumlahBelum],
    ];
    $anakDidikBelumDiobservasi = $anakDidikAktif->whereNotIn('id', $anakDidikSudahDiobservasi);

    // Build stats array: always show 'Anak Didik Aktif' and 'Perlu Observasi' (remove Program Konsultan card)
    $stats = [
      [
        'label' => 'Anak Didik Aktif',
        'value' => $jumlahAnakDidikAktif,
        'color' => 'success',
        'icon' => 'ri-group-line',
      ],
      [
        'label' => 'Perlu Observasi',
        'value' => $jumlahBelumUntukKonsultan,
        'color' => 'danger',
        'icon' => 'ri-alert-line',
      ],
    ];
    $isKonsultanPendidikanFlag = in_array($spec, ['pendidikan']);
    $dashboardData = [
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
      'stats' => $stats,
      'anakDidikBelumDiobservasi' => $anakDidikBelumDiobservasi,
      'pieChartData' => $pieChartData,
      'isKonsultanPendidikan' => $isKonsultanPendidikanFlag,
    ];

    return view('content.dashboard.konsultan-dashboard', compact('dashboardData', 'user'));
  }
}
