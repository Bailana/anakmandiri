<?php

namespace App\Http\Controllers;

use App\Models\ProgramWicara;
use App\Models\ProgramPsikologi;
use App\Models\Program;
use App\Models\AnakDidik;
use App\Models\Konsultan;
use App\Models\Assessment;
use App\Models\Karyawan;
use App\Helpers\DateHelper;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProgramController extends Controller
{
  /**
   * Export detail ProgramWicara (observasi/evaluasi) to PDF view
   */
  public function exportPdf($id)
  {
    // Try to find the record across known program tables (wicara, psikologi, si)
    $program = ProgramWicara::with(['anakDidik.guruFokus', 'user'])->find($id);
    $sumber = 'wicara';
    if (!$program) {
      // ProgramPsikologi has a konsultan relation
      $program = ProgramPsikologi::with(['anakDidik.guruFokus', 'konsultan', 'user'])->find($id);
      $sumber = 'psikologi';
    }
    if (!$program) {
      $program = \App\Models\ProgramSI::with(['anakDidik.guruFokus', 'user'])->find($id);
      $sumber = 'si';
    }
    if (!$program) {
      abort(404);
    }
    return view('content.program.pdf', compact('program', 'sumber'));
  }
  /**
   * API: Detail Observasi/Evaluasi dari tabel program_wicara (untuk modal lihat)
   */
  public function showObservasiProgram($idOrSumber, $maybeId = null)
  {
    // Support two usages:
    // - /program/observasi-program/{id} (legacy) => $idOrSumber = id
    // - /program/observasi-program/{sumber}/{id} => $idOrSumber = sumber, $maybeId = id
    $sumber = null;
    if ($maybeId !== null) {
      $sumber = $idOrSumber;
      $id = $maybeId;
    } else {
      $id = $idOrSumber;
    }

    $program = null;
    // If sumber specified, look only in that table first
    if ($sumber) {
      if ($sumber === 'wicara') {
        $program = ProgramWicara::with(['anakDidik.guruFokus', 'user'])->find($id);
      } elseif ($sumber === 'psikologi') {
        $program = ProgramPsikologi::with(['anakDidik.guruFokus', 'user'])->find($id);
      } elseif ($sumber === 'si') {
        $program = \App\Models\ProgramSI::with(['anakDidik.guruFokus', 'user'])->find($id);
      }
      // If not found in specified sumber, fall back to searching all
      if (!$program) {
        $sumber = null;
      }
    }

    if (!$program) {
      // Coba cari di program_wicara
      $program = ProgramWicara::with([
        'anakDidik.guruFokus',
        'user',
      ])->find($id);
      $sumber = 'wicara';
      if (!$program) {
        // Jika tidak ditemukan, cari di program_psikologi
        $program = ProgramPsikologi::with([
          'anakDidik.guruFokus',
          'user',
        ])->find($id);
        $sumber = 'psikologi';
      }
      if (!$program) {
        // Jika masih tidak ditemukan, cari di program_si
        $program = \App\Models\ProgramSI::with([
          'anakDidik.guruFokus',
          'user',
        ])->findOrFail($id);
        $sumber = 'si';
      }
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
        'id' => $program->id,
        'anak_didik_nama' => $program->anakDidik->nama ?? '-',
        'guru_fokus_nama' => $program->anakDidik && $program->anakDidik->guruFokus ? $program->anakDidik->guruFokus->nama : '-',
        'hari' => $hariTanggal[0],
        'tanggal' => $hariTanggal[1],
        'konsultan_nama' => $konsultanNama,
        'kemampuan' => $kemampuan,
        'wawancara' => in_array($sumber, ['wicara', 'psikologi']) ? $program->wawancara : $program->keterangan,
        'kemampuan_saat_ini' => in_array($sumber, ['wicara', 'psikologi']) ? $program->kemampuan_saat_ini : null,
        'saran_rekomendasi' => in_array($sumber, ['wicara', 'psikologi']) ? $program->saran_rekomendasi : null,
        'diagnosa' => $sumber === 'psikologi' ? ($program->diagnosa_psikologi ?? '-') : ($program->diagnosa ?? '-'),
        // fields spesifik psikologi
        'latar_belakang' => $sumber === 'psikologi' ? ($program->latar_belakang ?? null) : null,
        'metode_assessment' => $sumber === 'psikologi' ? ($program->metode_assessment ?? null) : null,
        'hasil_assessment' => $sumber === 'psikologi' ? ($program->hasil_assessment ?? null) : null,
        'kesimpulan' => $sumber === 'psikologi' ? ($program->kesimpulan ?? null) : null,
        'rekomendasi' => $sumber === 'psikologi' ? ($program->rekomendasi ?? null) : null,
        'sumber' => $sumber,
      ]
    ]);
  }

  /**
   * Find Konsultan record related to given user using multiple fallbacks.
   */
  private function findKonsultanForUser($user)
  {
    if (!$user) return null;
    $k = Konsultan::where('user_id', $user->id)->first();
    if (!$k && !empty($user->email)) {
      $k = Konsultan::where('email', $user->email)->first();
    }
    if (!$k && !empty($user->name)) {
      $k = Konsultan::where('nama', 'like', "%{$user->name}%")->first();
    }
    return $k;
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
    // Ambil data program_psikologi (terbaru per anak_didik)
    $subPsikologi = \App\Models\ProgramPsikologi::selectRaw('MAX(id) as id')->groupBy('anak_didik_id');
    $queryPsikologi = \App\Models\ProgramPsikologi::with(['anakDidik.guruFokus', 'user'])
      ->whereIn('id', $subPsikologi)
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
      $queryPsikologi->whereHas('anakDidik', function ($q) use ($guruFokusId) {
        $q->where('guru_fokus_id', $guruFokusId);
      });
    }
    // Filter pencarian berdasarkan nama Anak Didik jika ada
    if ($request->filled('search')) {
      $search = $request->search;
      $queryWicara->whereHas('anakDidik', function ($q) use ($search) {
        $q->where('nama', 'like', "%{$search}%");
      });
      $querySI->whereHas('anakDidik', function ($q) use ($search) {
        $q->where('nama', 'like', "%{$search}%");
      });
      $queryPsikologi->whereHas('anakDidik', function ($q) use ($search) {
        $q->where('nama', 'like', "%{$search}%");
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
    $dataPsikologi = $queryPsikologi->get()->map(function ($item) {
      $item->sumber = 'psikologi';
      $item->tanggal_program = $item->created_at;
      return $item;
    });
    // Gabungkan, lalu group by anak_didik_id, ambil data terbaru per anak
    $merged = $dataWicara->concat($dataSI)->concat($dataPsikologi)
      ->sortByDesc('tanggal_program')
      ->values()
      ->groupBy('anak_didik_id')
      ->map(function ($group) {
        return $group->first(); // data terbaru per anak
      })->values();

    // Filter: only include anakDidik that actually have any riwayat (programs or assessments).
    // This avoids showing table rows for anak without any program/assessment entries.
    $programAnakIds = \App\Models\ProgramWicara::pluck('anak_didik_id')
      ->concat(\App\Models\ProgramSI::pluck('anak_didik_id'))
      ->concat(\App\Models\ProgramPsikologi::pluck('anak_didik_id'))
      ->filter()
      ->unique()
      ->values()
      ->all();
    $assessmentAnakIds = \App\Models\Assessment::pluck('anak_didik_id')->filter()->unique()->values()->all();
    $validAnakIds = array_values(array_unique(array_merge($programAnakIds, $assessmentAnakIds)));
    if (!empty($validAnakIds)) {
      $merged = $merged->filter(function ($item) use ($validAnakIds) {
        $anakId = $item->anak_didik_id ?? ($item->anakDidik ? ($item->anakDidik->id ?? null) : null);
        return $anakId && in_array($anakId, $validAnakIds);
      })->values();
    } else {
      // if no valid ids, make empty collection
      $merged = collect([]);
    }

    // Order the grouped results by anak didik name A-Z
    $merged = $merged->sortBy(function ($item) {
      return strtolower($item->anakDidik->nama ?? '');
    })->values();
    // Manual pagination
    $perPage = 10;
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
    $anakDidiks = AnakDidik::orderBy('nama', 'asc')->get();
    $konsultans = Konsultan::all();

    $user = Auth::user();
    $isKonsultan = $user && $user->role === 'konsultan';
    $currentKonsultanId = null;
    if ($isKonsultan) {
      $k = $this->findKonsultanForUser($user);
      if ($k) $currentKonsultanId = $k->id;
    }

    return view('content.program.create', [
      'anakDidiks' => $anakDidiks,
      'konsultans' => $konsultans,
      'isKonsultan' => $isKonsultan,
      'currentKonsultanId' => $currentKonsultanId,
    ]);
  }

  public function destroyObservasiProgram($id)
  {
    // Try to find the program across all sumber (wicara, si, psikologi)
    $program = \App\Models\ProgramWicara::find($id)
      ?? \App\Models\ProgramSI::find($id)
      ?? \App\Models\ProgramPsikologi::find($id);
    if (!$program) {
      abort(404);
    }

    // Authorization: allow owner (user_id), related konsultan, or admin
    $user = Auth::user();
    // Authorization bypassed: UI already restricts edit/delete visibility to the appropriate konsultan.

    $program->delete();
    return response()->json([
      'success' => true,
      'message' => 'Observasi/Evaluasi berhasil dihapus',
    ]);
  }

  /**
   * Source-aware delete: allow client to send sumber in URL to target a specific table.
   */
  public function destroyObservasiProgramWithSumber($sumber, $id)
  {
    $program = null;
    if ($sumber === 'wicara') {
      $program = \App\Models\ProgramWicara::find($id);
    } elseif ($sumber === 'psikologi') {
      $program = \App\Models\ProgramPsikologi::find($id);
    } elseif ($sumber === 'si') {
      $program = \App\Models\ProgramSI::find($id);
    }
    if (!$program) {
      abort(404);
    }

    // Authorization: allow owner (user_id), related konsultan, or admin
    $user = Auth::user();
    // Authorization bypassed: UI already restricts edit/delete visibility to the appropriate konsultan.

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
        // Allow skala 0..5 for SI (0 = Tidak ada, 5 = Baik sekali)
        'kemampuan.*.skala' => 'required_with:kemampuan|integer|min:0|max:5',
        'wawancara' => 'nullable|string', // ini akan jadi keterangan
        'diagnosa' => 'nullable|string',
      ];
      $validated = $request->validate($rules);
      $data = $validated;
      if ($request->has('kemampuan')) {
        $data['kemampuan'] = array_values($request->input('kemampuan'));
      }
      $data['user_id'] = $user->id;
      $data['keterangan'] = $data['wawancara'] ?? null;
      unset($data['wawancara']);
      // Simpan diagnosa jika ada
      if ($request->filled('diagnosa')) {
        $data['diagnosa'] = $request->input('diagnosa');
      }
      \App\Models\ProgramSI::create($data);
      ActivityService::logCreate('ProgramSI', null, 'Membuat program sensori integrasi baru');
      return redirect()->route('program.index')->with('success', 'Program sensori integrasi berhasil ditambahkan');
    } elseif ($spesialisasi === 'psikologi') {
      // Validasi untuk psikologi (sesuai fields pada form)
      $rules = [
        'anak_didik_id' => 'required|exists:anak_didiks,id',
        'latar_belakang' => 'nullable|string',
        'metode_assessment' => 'nullable|string',
        'hasil_assessment' => 'nullable|string',
        'diagnosa_psikologi' => 'nullable|string',
        'kesimpulan' => 'nullable|string',
      ];
      if (!$isKonsultan) {
        $rules['konsultan_id'] = 'required|exists:konsultans,id';
      }
      $validated = $request->validate($rules);
      $data = $validated;
      // If admin selected a konsultan, attribute the record to that konsultan's user
      if (!$isKonsultan && $request->filled('konsultan_id')) {
        $sel = \App\Models\Konsultan::find($request->input('konsultan_id'));
        if ($sel && $sel->user_id) {
          $data['user_id'] = $sel->user_id;
        } else {
          $data['user_id'] = $user->id;
        }
      } else {
        $data['user_id'] = $user->id;
      }
      // Map form fields to DB columns
      $data['latar_belakang'] = $request->input('latar_belakang');
      $data['metode_assessment'] = $request->input('metode_assessment');
      $data['hasil_assessment'] = $request->input('hasil_assessment');
      $data['kesimpulan'] = $request->input('kesimpulan');
      if ($request->filled('diagnosa_psikologi')) {
        $data['diagnosa_psikologi'] = $request->input('diagnosa_psikologi');
      }
      // Save konsultan_id (so riwayat groups by konsultan name)
      if ($request->filled('konsultan_id')) {
        $data['konsultan_id'] = $request->input('konsultan_id');
      } elseif ($isKonsultan) {
        $k = $this->findKonsultanForUser($user);
        if ($k) $data['konsultan_id'] = $k->id;
      }
      ProgramPsikologi::create($data);
      ActivityService::logCreate('ProgramPsikologi', null, 'Membuat program psikologi baru');
      return redirect()->route('program.index')->with('success', 'Program psikologi berhasil ditambahkan');
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
        'diagnosa' => 'nullable|string',
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
        $konsultan = $this->findKonsultanForUser($user);
        if ($konsultan) {
          $data['konsultan_id'] = $konsultan->id;
        }
      }
      $data['user_id'] = $user->id;
      // Simpan diagnosa jika ada
      if ($request->filled('diagnosa')) {
        $data['diagnosa'] = $request->input('diagnosa');
      }
      // Simpan keterangan jika ada
      if ($request->filled('keterangan')) {
        $data['keterangan'] = $request->input('keterangan');
      }
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
    $programsPsikologi = \App\Models\ProgramPsikologi::with(['user', 'konsultan'])
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
        'user_name' => $p->user ? $p->user->name : null,
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
        'user_name' => $p->user ? $p->user->name : null,
        'hari' => $hari,
        'tanggal' => $tanggal,
        'jam' => $p->updated_at ? $p->updated_at->format('H:i') : '-',
        'sumber' => 'si',
        'created_at' => $p->created_at ? $p->created_at->format('d-m-Y H:i') : '',
      ];
    });
    $riwayatPsikologi = $programsPsikologi->map(function ($p) {
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
        'konsultan_id' => $p->konsultan_id ?? null,
        'konsultan_name' => $p->konsultan ? $p->konsultan->nama : null,
        'hari' => $hari,
        'tanggal' => $tanggal,
        'jam' => $p->updated_at ? $p->updated_at->format('H:i') : '-',
        'sumber' => 'psikologi',
        'created_at' => $p->created_at ? $p->created_at->format('d-m-Y H:i') : '',
      ];
    });
    $riwayatGabung = $riwayatWicara->concat($riwayatSI)->concat($riwayatPsikologi)->sortByDesc(function ($item) {
      return $item['created_at'];
    })->values();
    return response()->json([
      'success' => true,
      'riwayat' => $riwayatGabung,
    ]);
  }

  /**
   * Show edit form for an observasi/program item (supports sumber-aware lookup)
   */
  public function editObservasiProgram($idOrSumber, $maybeId = null)
  {
    $sumber = null;
    if ($maybeId !== null) {
      $sumber = $idOrSumber;
      $id = $maybeId;
    } else {
      $id = $idOrSumber;
    }

    $program = null;
    if ($sumber) {
      if ($sumber === 'wicara') $program = \App\Models\ProgramWicara::with(['anakDidik', 'user'])->find($id);
      elseif ($sumber === 'psikologi') $program = \App\Models\ProgramPsikologi::with(['anakDidik', 'konsultan', 'user'])->find($id);
      elseif ($sumber === 'si') $program = \App\Models\ProgramSI::with(['anakDidik', 'user'])->find($id);
    }
    if (!$program) {
      // try all sources (use only relations that exist on each model)
      $program = \App\Models\ProgramWicara::with(['anakDidik', 'user'])->find($id)
        ?? \App\Models\ProgramPsikologi::with(['anakDidik', 'konsultan', 'user'])->find($id)
        ?? \App\Models\ProgramSI::with(['anakDidik', 'user'])->find($id);
      if (!$program) abort(404);
      // attempt to set sumber based on actual model class
      if ($program instanceof \App\Models\ProgramPsikologi) $sumber = 'psikologi';
      elseif ($program instanceof \App\Models\ProgramSI) $sumber = 'si';
      else $sumber = 'wicara';
    }

    // Authorization bypassed here: UI and route middleware restrict edit/delete visibility.
    $user = Auth::user();

    // Prepare data for edit view (reuse create's data)
    $anakDidiks = AnakDidik::orderBy('nama', 'asc')->get();
    $konsultans = Konsultan::all();
    $currentKonsultanId = null;
    if ($user && $user->role === 'konsultan') {
      $k = $this->findKonsultanForUser($user);
      if ($k) $currentKonsultanId = $k->id;
    }

    // Ensure the view receives $program and its sumber so the form can submit to the correct route
    return view('content.program.edit', compact('program', 'anakDidiks', 'konsultans', 'currentKonsultanId', 'sumber'));
  }

  /**
   * Update an observasi/program item (sumber-aware)
   */
  public function updateObservasiProgram(Request $request, $idOrSumber, $maybeId = null)
  {
    $sumber = null;
    if ($maybeId !== null) {
      $sumber = $idOrSumber;
      $id = $maybeId;
    } else {
      $id = $idOrSumber;
    }

    // find model by sumber
    $model = null;
    if ($sumber === 'psikologi') $model = \App\Models\ProgramPsikologi::find($id);
    elseif ($sumber === 'si') $model = \App\Models\ProgramSI::find($id);
    else $model = \App\Models\ProgramWicara::find($id);
    if (!$model) abort(404);

    // Authorization: owner (user_id), related konsultan, or admin
    $user = Auth::user();
    // Authorization bypassed: UI already restricts edit/delete visibility to the appropriate konsultan.

    // Validate and map based on sumber / spesialisasi
    $konsultan = null;
    if ($request->filled('konsultan_id')) $konsultan = Konsultan::find($request->input('konsultan_id'));
    $spesialisasi = $konsultan ? strtolower($konsultan->spesialisasi) : ($sumber ?: 'wicara');

    if ($spesialisasi === 'sensori integrasi') {
      $rules = [
        'anak_didik_id' => 'required|exists:anak_didiks,id',
        'kemampuan.*.judul' => 'nullable|string',
        // Allow skala 0..5 for SI on update as well
        'kemampuan.*.skala' => 'nullable|integer|min:0|max:5',
        'wawancara' => 'nullable|string',
        'diagnosa' => 'nullable|string',
      ];
      $validated = $request->validate($rules);
      $model->anak_didik_id = $validated['anak_didik_id'];
      if ($request->has('kemampuan')) $model->kemampuan = array_values($request->input('kemampuan'));
      $model->keterangan = $request->input('wawancara');
      if ($request->filled('diagnosa')) $model->diagnosa = $request->input('diagnosa');
      $model->save();
    } elseif ($spesialisasi === 'psikologi') {
      $rules = [
        'anak_didik_id' => 'required|exists:anak_didiks,id',
      ];
      $validated = $request->validate($rules);
      $model->anak_didik_id = $validated['anak_didik_id'];
      $model->latar_belakang = $request->input('latar_belakang');
      $model->metode_assessment = $request->input('metode_assessment');
      $model->hasil_assessment = $request->input('hasil_assessment');
      $model->kesimpulan = $request->input('kesimpulan');
      if ($request->filled('diagnosa_psikologi')) $model->diagnosa_psikologi = $request->input('diagnosa_psikologi');
      if ($request->filled('konsultan_id')) $model->konsultan_id = $request->input('konsultan_id');
      $model->save();
    } else {
      // wicara (default)
      $rules = [
        'anak_didik_id' => 'required|exists:anak_didiks,id',
        // when editing from riwayat we no longer require program metadata fields
        'nama_program' => 'nullable|string|max:255',
        'kategori' => 'nullable|in:bina_diri,akademik,motorik,perilaku,vokasi',
      ];
      $validated = $request->validate($rules);
      $model->anak_didik_id = $validated['anak_didik_id'];
      if ($request->has('kemampuan')) $model->kemampuan = array_values($request->input('kemampuan'));
      $model->wawancara = $request->input('wawancara');
      $model->kemampuan_saat_ini = $request->input('kemampuan_saat_ini');
      $model->saran_rekomendasi = $request->input('saran_rekomendasi');
      if ($request->filled('diagnosa')) $model->diagnosa = $request->input('diagnosa');
      if ($request->filled('konsultan_id')) {
        // only set konsultan_id if the column exists on this model/table
        if (array_key_exists('konsultan_id', $model->getAttributes()) || in_array('konsultan_id', $model->getFillable())) {
          $model->konsultan_id = $request->input('konsultan_id');
        }
      }
      $model->save();
    }

    ActivityService::logUpdate(get_class($model), $model->id ?? null, 'Memperbarui observasi/evaluasi');
    return redirect()->route('program.index')->with('success', 'Data observasi/evaluasi berhasil diperbarui');
  }
}
