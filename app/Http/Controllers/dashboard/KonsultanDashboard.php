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
      } elseif (ProgramPendidikan::where('created_by', $user->id)->exists() || ProgramPendidikan::where('konsultan_id', $user->id)->exists()) {
        $spec = 'pendidikan';
      } else {
        $spec = '';
      }
    }


    // Grafik: jumlah anak didik yang sudah diobservasi/evaluasi oleh konsultan ini
    $chartCategories = [];
    $chartSeries = [];
    // Run records lookup when we have an inferred specialization ($spec)
    if ($spec) {
      if ($spec === 'psikologi') {
        if ($konsultanId) {
          $q = ProgramPsikologi::where('konsultan_id', $konsultanId);
        } else {
          $q = ProgramPsikologi::where('user_id', $user->id);
        }
        $records = $q->selectRaw('anak_didik_id, COUNT(*) as total')
          ->groupBy('anak_didik_id')
          ->with('anakDidik')
          ->get();
      } elseif ($spec === 'wicara') {
        $records = ProgramWicara::where('user_id', $user->id)
          ->selectRaw('anak_didik_id, COUNT(*) as total')
          ->groupBy('anak_didik_id')
          ->with('anakDidik')
          ->get();
      } elseif ($spec === 'sensori integrasi' || $spec === 'sensori_integrasi' || $spec === 'si') {
        $records = ProgramSI::where('user_id', $user->id)
          ->selectRaw('anak_didik_id, COUNT(*) as total')
          ->groupBy('anak_didik_id')
          ->with('anakDidik')
          ->get();
      } elseif ($spec === 'pendidikan') {
        if ($konsultanId) {
          $q = ProgramPendidikan::where('konsultan_id', $konsultanId);
        } else {
          $q = ProgramPendidikan::where('created_by', $user->id);
        }
        $records = $q->selectRaw('anak_didik_id, COUNT(*) as total')
          ->groupBy('anak_didik_id')
          ->with('anakDidik')
          ->get();
      } else {
        // fallback to assessments when no special program table applies
        if ($konsultanId) {
          $records = Assessment::where('konsultan_id', $konsultanId)
            ->selectRaw('anak_didik_id, COUNT(*) as total')
            ->groupBy('anak_didik_id')
            ->with('anakDidik')
            ->get();
        } else {
          $records = collect();
        }
      }

      foreach ($records as $r) {
        $chartCategories[] = $r->anakDidik ? $r->anakDidik->nama : 'Anak Didik';
        $chartSeries[] = $r->total;
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
      if ($konsultanId) {
        $anakDidikSudahDiobservasi = ProgramPendidikan::where('konsultan_id', $konsultanId)
          ->where('created_at', '>=', $sixMonthsAgo)
          ->distinct()->pluck('anak_didik_id')->toArray();
      } else {
        $anakDidikSudahDiobservasi = ProgramPendidikan::where('created_by', $user->id)
          ->where('created_at', '>=', $sixMonthsAgo)
          ->distinct()->pluck('anak_didik_id')->toArray();
      }
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
          // Show 'Perlu Observasi' for several konsultan specializations, otherwise show program count
          'label' => in_array($spec, ['psikologi', 'wicara', 'sensori integrasi', 'sensori_integrasi', 'si', 'pendidikan']) ? 'Perlu Observasi' : 'Program Konsultan',
          'value' => in_array($spec, ['psikologi', 'wicara', 'sensori integrasi', 'sensori_integrasi', 'si', 'pendidikan']) ? $jumlahBelumUntukKonsultan : $jumlahProgramKonsultan,
          'color' => in_array($spec, ['psikologi', 'wicara', 'sensori integrasi', 'sensori_integrasi', 'si', 'pendidikan']) ? 'danger' : 'info',
          'icon' => in_array($spec, ['psikologi', 'wicara', 'sensori integrasi', 'sensori_integrasi', 'si', 'pendidikan']) ? 'ri-alert-line' : 'ri-book-2-line',
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
