<?php

namespace App\Http\Controllers;

use App\Models\ProgramWicara;
use App\Models\Program;
use App\Models\AnakDidik;
use App\Models\Konsultan;
use App\Models\Assessment;
use App\Models\Karyawan;
use App\Helpers\DateHelper;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgramController extends Controller
{
  /**
   * Export detail ProgramWicara (observasi/evaluasi) to PDF view
   */
  public function exportPdf($id)
  {
    $program = ProgramWicara::with(['anakDidik.guruFokus', 'konsultan'])->findOrFail($id);
    return view('content.program.pdf', compact('program'));
  }
  /**
   * API: Detail Observasi/Evaluasi dari tabel program_wicara (untuk modal lihat)
   */
  public function showObservasiProgram($id)
  {
    // Coba cari di program_wicara
    $program = ProgramWicara::with([
      'anakDidik.guruFokus',
      'user',
    ])->find($id);
    $sumber = 'wicara';
    if (!$program) {
      // Jika tidak ditemukan, cari di program_si
      $program = \App\Models\ProgramSI::with([
        'anakDidik.guruFokus',
        'user',
      ])->findOrFail($id);
      $sumber = 'si';
    }

    $createdAt = $program->created_at ? $program->created_at->format('Y-m-d') : null;
    $hariTanggal = $createdAt ? \App\Helpers\DateHelper::hariTanggal($createdAt) : [null, null];

    $kemampuan = [];
    if (is_array($program->kemampuan)) {
      foreach ($program->kemampuan as $item) {
        $kemampuan[] = [
          'judul' => $item['judul'] ?? '-',
          'skala' => $item['skala'] ?? null,
        ];
      }
    }
    $konsultanNama = $program->user ? $program->user->name : '-';
    // Untuk program_si, wawancara = keterangan, tidak ada kemampuan_saat_ini/saran_rekomendasi
    return response()->json([
      'success' => true,
      'data' => [
        'anak_didik_nama' => $program->anakDidik->nama ?? '-',
        'guru_fokus_nama' => $program->anakDidik && $program->anakDidik->guruFokus ? $program->anakDidik->guruFokus->nama : '-',
        'hari' => $hariTanggal[0],
        'tanggal' => $hariTanggal[1],
        'konsultan_nama' => $konsultanNama,
        'kemampuan' => $kemampuan,
        'wawancara' => $sumber === 'wicara' ? $program->wawancara : $program->keterangan,
        'kemampuan_saat_ini' => $sumber === 'wicara' ? $program->kemampuan_saat_ini : null,
        'saran_rekomendasi' => $sumber === 'wicara' ? $program->saran_rekomendasi : null,
        'sumber' => $sumber,
      ]
    ]);
  }

  /**
   * API: Riwayat Observasi/Evaluasi untuk Anak Didik tertentu
   */
  public function riwayatObservasi($anakDidikId)
  {
    $assessments = Assessment::with(['konsultan', 'anakDidik.guruFokus'])
      ->where('anak_didik_id', $anakDidikId)
      ->orderByDesc('tanggal_assessment')
      ->get();

    $riwayat = $assessments->map(function ($a) {
      [$hari, $tanggal] = $a->tanggal_assessment
        ? DateHelper::hariTanggal($a->tanggal_assessment->format('Y-m-d'))
        : [null, null];
      return [
        'id' => $a->id,
        'hari' => $hari,
        'tanggal' => $tanggal,
        'created_at' => $a->created_at ? $a->created_at->format('d-m-Y H:i') : '',
        'guru_fokus' => $a->anakDidik && $a->anakDidik->guruFokus ? $a->anakDidik->guruFokus->nama : null,
      ];
    });
    return response()->json([
      'success' => true,
      'riwayat' => $riwayat,
    ]);
  }

  /**
   * Hapus assessment (observasi/evaluasi) dari modal riwayat
   */
  public function destroyObservasi($id)
  {
    $assessment = Assessment::findOrFail($id);
    $anakDidikId = $assessment->anak_didik_id;
    $assessment->delete();
    return response()->json([
      'success' => true,
      'anak_didik_id' => $anakDidikId,
    ]);
  }
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $user = Auth::user();
    // Ambil data program_wicara (terbaru per anak_didik)
    $subWicara = \App\Models\ProgramWicara::selectRaw('MAX(id) as id')->groupBy('anak_didik_id');
    $queryWicara = \App\Models\ProgramWicara::with(['anakDidik.guruFokus', 'user'])
      ->whereIn('id', $subWicara)
      ->whereHas('anakDidik');
    // Ambil data program_si (terbaru per anak_didik)
    $subSI = \App\Models\ProgramSI::selectRaw('MAX(id) as id')->groupBy('anak_didik_id');
    $querySI = \App\Models\ProgramSI::with(['anakDidik.guruFokus', 'user'])
      ->whereIn('id', $subSI)
      ->whereHas('anakDidik');
    // Filter guru fokus jika ada
    if ($request->filled('guru_fokus')) {
      $guruFokusId = $request->guru_fokus;
      $queryWicara->whereHas('anakDidik', function ($q) use ($guruFokusId) {
        $q->where('guru_fokus_id', $guruFokusId);
      });
      $querySI->whereHas('anakDidik', function ($q) use ($guruFokusId) {
        $q->where('guru_fokus_id', $guruFokusId);
      });
    }
    // Ambil data, tambahkan field sumber
    $dataWicara = $queryWicara->get()->map(function ($item) {
      $item->sumber = 'wicara';
      $item->tanggal_program = $item->created_at;
      return $item;
    });
    $dataSI = $querySI->get()->map(function ($item) {
      $item->sumber = 'si';
      $item->tanggal_program = $item->created_at;
      return $item;
    });
    // Gabungkan, lalu group by anak_didik_id, ambil data terbaru per anak
    $merged = $dataWicara->concat($dataSI)
      ->sortByDesc('tanggal_program')
      ->values()
      ->groupBy('anak_didik_id')
      ->map(function ($group) {
        return $group->first(); // data terbaru per anak
      })->values();
    // Manual pagination
    $perPage = 15;
    $currentPage = $request->input('page', 1);
    $total = $merged->count();
    $items = $merged->slice(($currentPage - 1) * $perPage, $perPage)->all();
    $programs = new \Illuminate\Pagination\LengthAwarePaginator($items, $total, $perPage, $currentPage, [
      'path' => $request->url(),
      'query' => $request->query(),
    ]);
    // Guru Fokus options
    $guruOptions = \App\Models\Karyawan::where('posisi', 'Guru Fokus')->orderBy('nama')->pluck('nama', 'id');
    $data = [
      'title' => 'Program Wicara',
      'programs' => $programs,
      'guruOptions' => $guruOptions,
    ];
    return view('content.program.index', $data);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    $anakDidiks = AnakDidik::all();
    $konsultans = Konsultan::all();

    return view('content.program.create', [
      'anakDidiks' => $anakDidiks,
      'konsultans' => $konsultans,
    ]);
  }

  public function destroyObservasiProgram($id)
  {
    $program = \App\Models\ProgramWicara::findOrFail($id);
    $program->delete();
    return response()->json([
      'success' => true,
      'message' => 'Observasi/Evaluasi berhasil dihapus',
    ]);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $user = Auth::user();
    $isKonsultan = $user->role === 'konsultan';
    $konsultanId = $request->input('konsultan_id');
    $konsultan = $konsultanId ? \App\Models\Konsultan::find($konsultanId) : null;
    $spesialisasi = $konsultan ? strtolower($konsultan->spesialisasi) : '';

    if ($spesialisasi === 'sensori integrasi') {
      // Validasi untuk sensori integrasi
      $rules = [
        'anak_didik_id' => 'required|exists:anak_didiks,id',
        'kemampuan' => 'nullable|array',
        'kemampuan.*.judul' => 'required_with:kemampuan|string',
        'kemampuan.*.skala' => 'required_with:kemampuan|integer|min:1|max:5',
        'wawancara' => 'nullable|string', // ini akan jadi keterangan
      ];
      $validated = $request->validate($rules);
      $data = $validated;
      if ($request->has('kemampuan')) {
        $data['kemampuan'] = array_values($request->input('kemampuan'));
      }
      $data['user_id'] = $user->id;
      $data['keterangan'] = $data['wawancara'] ?? null;
      unset($data['wawancara']);
      \App\Models\ProgramSI::create($data);
      ActivityService::logCreate('ProgramSI', null, 'Membuat program sensori integrasi baru');
      return redirect()->route('program.index')->with('success', 'Program sensori integrasi berhasil ditambahkan');
    } else {
      // Validasi untuk wicara (default)
      $rules = [
        'anak_didik_id' => 'required|exists:anak_didiks,id',
        'deskripsi' => 'nullable|string',
        'target_pembelajaran' => 'nullable|string',
        'tanggal_mulai' => 'nullable|date',
        'tanggal_selesai' => 'nullable|date',
        'catatan_konsultan' => 'nullable|string',
        'kemampuan' => 'nullable|array',
        'kemampuan.*.judul' => 'required_with:kemampuan|string',
        'kemampuan.*.skala' => 'required_with:kemampuan|integer|min:1|max:5',
        'wawancara' => 'nullable|string',
        'kemampuan_saat_ini' => 'nullable|string',
        'saran_rekomendasi' => 'nullable|string',
      ];
      if (!$isKonsultan) {
        $rules['konsultan_id'] = 'required|exists:konsultans,id';
        $rules['nama_program'] = 'required|string|max:255';
        $rules['kategori'] = 'required|in:bina_diri,akademik,motorik,perilaku,vokasi';
      }
      $validated = $request->validate($rules);
      $data = $validated;
      if ($request->has('kemampuan')) {
        $data['kemampuan'] = array_values($request->input('kemampuan'));
      }
      if ($isKonsultan) {
        $konsultan = \App\Models\Konsultan::where('user_id', $user->id)->first();
        if ($konsultan) {
          $data['konsultan_id'] = $konsultan->id;
        }
      }
      $data['user_id'] = $user->id;
      ProgramWicara::create($data);
      ActivityService::logCreate('ProgramWicara', null, 'Membuat program wicara baru');
      return redirect()->route('program.index')->with('success', 'Program pembelajaran berhasil ditambahkan');
    }
  }

  /**
   * API: Riwayat Observasi/Evaluasi dari tabel programs untuk Anak Didik tertentu
   */
  public function riwayatObservasiProgram($anakDidikId)
  {
    $programsWicara = \App\Models\ProgramWicara::with('user')
      ->where('anak_didik_id', $anakDidikId)
      ->orderByDesc('created_at')
      ->get();
    $programsSI = \App\Models\ProgramSI::with('user')
      ->where('anak_didik_id', $anakDidikId)
      ->orderByDesc('created_at')
      ->get();

    $riwayatWicara = $programsWicara->map(function ($p) {
      if ($p->created_at) {
        $tanggalStr = is_string($p->created_at) ? $p->created_at : $p->created_at->format('Y-m-d');
        [$hari, $tanggal] = \App\Helpers\DateHelper::hariTanggal(date('Y-m-d', strtotime($tanggalStr)));
      } else {
        $hari = null;
        $tanggal = null;
      }
      return [
        'id' => $p->id,
        'user_id' => $p->user_id,
        'user_name' => $p->user ? $p->user->name : '-',
        'hari' => $hari,
        'tanggal' => $tanggal,
        'jam' => $p->updated_at ? $p->updated_at->format('H:i') : '-',
        'sumber' => 'wicara',
        'created_at' => $p->created_at ? $p->created_at->format('d-m-Y H:i') : '',
      ];
    });
    $riwayatSI = $programsSI->map(function ($p) {
      if ($p->created_at) {
        $tanggalStr = is_string($p->created_at) ? $p->created_at : $p->created_at->format('Y-m-d');
        [$hari, $tanggal] = \App\Helpers\DateHelper::hariTanggal(date('Y-m-d', strtotime($tanggalStr)));
      } else {
        $hari = null;
        $tanggal = null;
      }
      return [
        'id' => $p->id,
        'user_id' => $p->user_id,
        'user_name' => $p->user ? $p->user->name : '-',
        'hari' => $hari,
        'tanggal' => $tanggal,
        'jam' => $p->updated_at ? $p->updated_at->format('H:i') : '-',
        'sumber' => 'si',
        'created_at' => $p->created_at ? $p->created_at->format('d-m-Y H:i') : '',
      ];
    });
    $riwayatGabung = $riwayatWicara->concat($riwayatSI)->sortByDesc(function ($item) {
      return $item['created_at'];
    })->values();
    return response()->json([
      'success' => true,
      'riwayat' => $riwayatGabung,
    ]);
  }
}
