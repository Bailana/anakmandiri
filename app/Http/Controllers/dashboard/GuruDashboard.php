<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\GuruAnakDidik;
use App\Models\Karyawan;
use App\Models\AnakDidik;

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

    // try counting anak didik via anak_didiks.guru_fokus_id -> karyawan
    $anakCount = 0;
    try {
      $karyawanId = Karyawan::where('user_id', $user->id)->value('id');
      if (!$karyawanId && $user->email) {
        $karyawanId = Karyawan::where('email', $user->email)->value('id');
      }
      if (!$karyawanId) {
        $karyawanId = Karyawan::where('nama', $user->name)->value('id');
      }

      if ($karyawanId) {
        $anakCount = AnakDidik::where('guru_fokus_id', $karyawanId)
          ->where(function ($q) {
            $q->whereNull('status')->orWhere('status', 'aktif');
          })
          ->count();
      } else {
        // fallback to previous approach using GuruAnakDidik assignments
        $anakCount = GuruAnakDidik::where('user_id', $user->id)
          ->where('status', 'aktif')
          ->groupBy('anak_didik_id')
          ->get()
          ->count();
      }
    } catch (\Exception $e) {
      $anakCount = 0;
    }

    $dashboardData = [
      'role' => 'guru',
      'message' => 'Selamat datang di Dashboard Guru',
      'chartData' => [
        'title' => 'Perkembangan Anak Didik per Bulan (0-4)',
        'categories' => $categories,
        'series' => $series
      ]
    ];

    // prepare anak list for select (try via guru_fokus_id else via assignments)
    $anakList = [];
    try {
      if (!empty($karyawanId)) {
        $anakList = AnakDidik::where('guru_fokus_id', $karyawanId)->orderBy('nama')->get();
      }
      if (empty($anakList) || count($anakList) === 0) {
        $anakList = GuruAnakDidik::where('user_id', $user->id)->where('status', 'aktif')->with('anakDidik')->get()->pluck('anakDidik')->filter()->values();
      }
    } catch (\Exception $e) {
      $anakList = [];
    }

    return view('content.dashboard.guru-dashboard', compact('dashboardData', 'user', 'anakCount', 'anakList'));
  }

  /**
   * Return programs (ProgramAnak) for a given anak id
   */
  public function programsForAnak($anakId)
  {
    try {
      $items = \App\Models\ProgramAnak::where('anak_didik_id', $anakId)->orderBy('nama_program')->get();
      $out = $items->map(function ($it) {
        return ['id' => $it->id, 'nama_program' => $it->nama_program];
      });
      return response()->json(['success' => true, 'programs' => $out]);
    } catch (\Exception $e) {
      return response()->json(['success' => false, 'message' => 'Gagal mengambil program'], 500);
    }
  }

  /**
   * Return chart data for a given anak, kategori and program
   */
  public function chartDataForAnak(\Illuminate\Http\Request $request)
  {
    $anakId = $request->query('anak_id');
    $kategori = $request->query('kategori');
    $programId = $request->query('program_id');

    if (!$anakId || !$kategori || !$programId) {
      return response()->json(['success' => false, 'message' => 'Missing parameters'], 400);
    }

    // fetch assessments matching criteria and return labels (dates) and series (perkembangan)
    $assess = \App\Models\Assessment::where('anak_didik_id', $anakId)
      ->where('kategori', $kategori)
      ->where('program_id', $programId)
      ->orderBy('tanggal_assessment')
      ->get();

    $labels = [];
    $data = [];
    foreach ($assess as $a) {
      $labels[] = $a->tanggal_assessment ? $a->tanggal_assessment->toDateString() : ($a->created_at ? $a->created_at->toDateString() : '');
      $data[] = is_numeric($a->perkembangan) ? (float)$a->perkembangan : 0;
    }

    return response()->json(['success' => true, 'labels' => $labels, 'data' => $data]);
  }
}
