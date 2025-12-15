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
    $program = ProgramWicara::with([
      'anakDidik.guruFokus',
      'konsultan',
    ])->findOrFail($id);

    // Hari/tanggal observasi dari created_at
    $createdAt = $program->created_at ? $program->created_at->format('Y-m-d') : null;
    $hariTanggal = $createdAt ? \App\Helpers\DateHelper::hariTanggal($createdAt) : [null, null];

    // Siapkan data kemampuan (tabel penilaian kemampuan)
    $kemampuan = [];
    if (is_array($program->kemampuan)) {
      foreach ($program->kemampuan as $item) {
        $kemampuan[] = [
          'judul' => $item['judul'] ?? '-',
          'skala' => $item['skala'] ?? null,
        ];
      }
    }

    return response()->json([
      'success' => true,
      'data' => [
        'anak_didik_nama' => $program->anakDidik->nama ?? '-',
        'guru_fokus_nama' => $program->anakDidik && $program->anakDidik->guruFokus ? $program->anakDidik->guruFokus->nama : '-',
        'hari' => $hariTanggal[0],
        'tanggal' => $hariTanggal[1],
        'konsultan_nama' => $program->konsultan->nama ?? '-',
        'kemampuan' => $kemampuan,
        'wawancara' => $program->wawancara,
        'kemampuan_saat_ini' => $program->kemampuan_saat_ini,
        'saran_rekomendasi' => $program->saran_rekomendasi,
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
    // Ambil hanya anak didik yang masih punya riwayat observasi/evaluasi
    $sub = ProgramWicara::selectRaw('MAX(id) as id')
      ->groupBy('anak_didik_id');
    $query = ProgramWicara::with(['anakDidik.guruFokus', 'konsultan'])
      ->whereIn('id', $sub)
      ->whereHas('anakDidik') // pastikan relasi anak didik masih ada
      ->whereExists(function ($q) {
        $q->selectRaw(1)
          ->from('program_wicara as pw2')
          ->whereRaw('pw2.anak_didik_id = program_wicara.anak_didik_id');
      });
    if ($request->filled('guru_fokus')) {
      $guruFokusId = $request->guru_fokus;
      $query->whereHas('anakDidik', function ($q) use ($guruFokusId) {
        $q->where('guru_fokus_id', $guruFokusId);
      });
    }
    $programs = $query->paginate(15)->appends($request->query());
    // Guru Fokus options (Karyawan dengan posisi Guru Fokus saja)
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
    $validated = $request->validate([
      'anak_didik_id' => 'required|exists:anak_didiks,id',
      'kemampuan' => 'nullable|array',
      'kemampuan.*.judul' => 'required_with:kemampuan|string',
      'kemampuan.*.skala' => 'required_with:kemampuan|integer|min:1|max:5',
      'wawancara' => 'nullable|string',
      'kemampuan_saat_ini' => 'nullable|string',
      'saran_rekomendasi' => 'nullable|string',
    ]);
    $data = $validated;
    if ($request->has('kemampuan')) {
      $data['kemampuan'] = array_values($request->input('kemampuan'));
    }
    // Jika user adalah konsultan, isi konsultan_id otomatis
    if ($isKonsultan) {
      $konsultan = \App\Models\Konsultan::where('user_id', $user->id)->first();
      if ($konsultan) {
        $data['konsultan_id'] = $konsultan->id;
      }
    }
    ProgramWicara::create($data);

    // Log activity
    ActivityService::logCreate('ProgramWicara', null, 'Membuat program wicara baru');

    return redirect()->route('program.index')->with('success', 'Program pembelajaran berhasil ditambahkan');
  }

  /**
   * API: Riwayat Observasi/Evaluasi dari tabel programs untuk Anak Didik tertentu
   */
  public function riwayatObservasiProgram($anakDidikId)
  {
    $programs = ProgramWicara::where('anak_didik_id', $anakDidikId)
      ->orderByDesc('created_at')
      ->get();

    $riwayat = $programs->map(function ($p) {
      if ($p->created_at) {
        $tanggalStr = is_string($p->created_at) ? $p->created_at : $p->created_at->format('Y-m-d');
        [$hari, $tanggal] = \App\Helpers\DateHelper::hariTanggal(date('Y-m-d', strtotime($tanggalStr)));
      } else {
        $hari = null;
        $tanggal = null;
      }
      return [
        'id' => $p->id,
        'hari' => $hari,
        'tanggal' => $tanggal,
        'jam' => $p->updated_at ? $p->updated_at->format('H:i') : '-',
        'kategori' => $p->kategori ?? '-',
        'catatan_konsultan' => $p->catatan_konsultan ?? '-',
        'created_at' => $p->created_at ? $p->created_at->format('d-m-Y H:i') : '',
      ];
    });
    return response()->json([
      'success' => true,
      'riwayat' => $riwayat,
    ]);
  }
}
