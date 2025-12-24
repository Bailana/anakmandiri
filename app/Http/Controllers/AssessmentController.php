<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AnakDidik;
use App\Models\Konsultan;
use App\Models\GuruAnakDidik;
use App\Models\GuruAnakDidikApproval;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PpiItem;
use App\Models\Program;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AssessmentController extends Controller
{
  /**
   * Export detail assessment to PDF view
   */
  public function exportPdf($id)
  {
    $assessment = Assessment::with('anakDidik', 'konsultan')->findOrFail($id);
    return view('content.assessment.pdf', compact('assessment'));
  }
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $query = Assessment::with('anakDidik', 'konsultan');

    // Filter by konsultan for non-admin
    $user = Auth::user();
    if ($user->role === 'konsultan') {
      $konsultan = Konsultan::where('user_id', $user->id)->first();
      if ($konsultan) {
        $query->where('konsultan_id', $konsultan->id);
      }
    }

    // Search by nama anak
    if ($request->filled('search')) {
      $search = $request->search;
      $query->whereHas('anakDidik', function ($q) use ($search) {
        $q->where('nama', 'like', "%{$search}%")
          ->orWhere('nis', 'like', "%{$search}%");
      });
    }

    // Filter by kategori
    if ($request->filled('kategori')) {
      $query->where('kategori', $request->kategori);
    }

    $assessments = $query->paginate(15)->appends($request->query());

    $data = [
      'title' => 'Penilaian Anak',
      'assessments' => $assessments,
    ];

    return view('content.assessment.index', $data);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    $user = Auth::user();
    // For guru users, only show children assigned to them or those they have approved access for
    if ($user && $user->role === 'guru') {
      $assignedIds = GuruAnakDidik::where('user_id', $user->id)->where('status', 'aktif')->pluck('anak_didik_id')->toArray();
      $approvedIds = GuruAnakDidikApproval::where('requester_user_id', $user->id)->where('status', 'approved')->pluck('anak_didik_id')->toArray();

      // also include anak didik where this guru is recorded as guru_fokus (via Karyawan mapping)
      $karyawan = Karyawan::where('nama', $user->name)->first();
      $fokusIds = [];
      if ($karyawan) {
        $fokusIds = AnakDidik::where('guru_fokus_id', $karyawan->id)->pluck('id')->toArray();
      }

      $ids = array_unique(array_merge($assignedIds, $approvedIds, $fokusIds));
      if (count($ids) > 0) {
        $anakDidiks = AnakDidik::whereIn('id', $ids)->orderBy('nama')->get();
      } else {
        // return empty collection to view
        $anakDidiks = collect();
      }
    } else {
      $anakDidiks = AnakDidik::orderBy('nama')->get();
    }

    $konsultans = Konsultan::orderBy('nama')->get();

    return view('content.assessment.create', [
      'anakDidiks' => $anakDidiks,
      'konsultans' => $konsultans,
    ]);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {

    $validated = $request->validate([
      'anak_didik_id' => 'required|exists:anak_didiks,id',
      'konsultan_id' => 'nullable|exists:konsultans,id',
      'program_id' => 'nullable|exists:programs,id',
      'kategori' => 'required|in:bina_diri,akademik,motorik,perilaku,vokasi',
      'perkembangan' => 'nullable|integer|min:1|max:4',
      'aktivitas' => 'nullable|string',
      'hasil_penilaian' => 'nullable|string',
      'rekomendasi' => 'nullable|string',
      'saran' => 'nullable|string',
      'tanggal_assessment' => 'nullable|date',
      'kemampuan' => 'nullable|array',
      'kemampuan.*.judul' => 'nullable|string',
      'kemampuan.*.skala' => 'nullable|in:1,2,3,4,5',
    ]);

    // Ensure kemampuan is an array (store empty array if not provided)
    $validated['kemampuan'] = array_values($request->input('kemampuan', []));

    Assessment::create($validated);

    return redirect()->route('assessment.index')->with('success', 'Penilaian berhasil ditambahkan');
  }

  /**
   * Return PPI programs for a given anak didik and kategori (creates Program rows if missing)
   */
  public function ppiPrograms(Request $request)
  {
    $anakId = $request->query('anak_didik_id');
    $kategori = $request->query('kategori');

    if (!$anakId || !$kategori) {
      return response()->json(['success' => false, 'programs' => []]);
    }

    // normalize kategori: user selects values like 'bina_diri' but PPI may store 'Bina Diri'
    $normalizedKategori = str_replace('_', ' ', $kategori);

    // match kategori case-insensitively against PPI items
    $itemsQuery = PpiItem::whereHas('ppi', function ($q) use ($anakId) {
      $q->where('anak_didik_id', $anakId);
    })->whereRaw('LOWER(kategori) = ?', [strtolower($normalizedKategori)]);

    $items = $itemsQuery->get();

    // fallback: if none found, try a LIKE match on kategori
    if ($items->isEmpty()) {
      $items = PpiItem::whereHas('ppi', function ($q) use ($anakId) {
        $q->where('anak_didik_id', $anakId);
      })->where('kategori', 'like', "%{$normalizedKategori}%")->get();
    }

    // If `programs` table exists, try to ensure a Program record; otherwise return names from PPI items
    $names = [];
    foreach ($items as $it) {
      $name = trim($it->nama_program ?? '');
      if ($name === '') continue;
      $names[$name] = $name;
    }

    $result = [];
    if (Schema::hasTable('programs')) {
      foreach ($names as $name) {
        $prog = Program::firstOrCreate([
          'anak_didik_id' => $anakId,
          'nama_program' => $name,
          'kategori' => $kategori,
        ], [
          'konsultan_id' => null,
          'deskripsi' => null,
        ]);
        $result[] = ['id' => $prog->id, 'nama_program' => $prog->nama_program];
      }
    } else {
      // return names with null id so frontend can display them; selection will leave program_id null
      foreach ($names as $name) {
        $result[] = ['id' => null, 'nama_program' => $name];
      }
    }

    return response()->json(['success' => true, 'programs' => $result]);
  }

  /**
   * Display the specified resource.
   */
  public function show(string $id)
  {
    $assessment = Assessment::with('anakDidik', 'konsultan')->findOrFail($id);
    return response()->json([
      'success' => true,
      'data' => $assessment
    ]);
  }

  /**
   * Return per-program assessment history for a given anak didik
   * Response: { success: true, programs: [ { id, nama_program, datapoints: [{tanggal, score}] } ] }
   */
  public function programHistory($anakId)
  {
    $assessments = Assessment::with('program')
      ->where('anak_didik_id', $anakId)
      ->orderBy('tanggal_assessment')
      ->get();

    $grouped = [];
    foreach ($assessments as $a) {
      $progName = $a->program ? $a->program->nama_program : ($a->program_id ? ('Program #' . $a->program_id) : 'Umum');
      $pid = $a->program_id ?? 'general';

      // determine a numeric score: prefer 'perkembangan', else average kemampuan skala
      $score = null;
      if (!is_null($a->perkembangan)) {
        $score = (float) $a->perkembangan;
      } elseif (is_array($a->kemampuan) && count($a->kemampuan) > 0) {
        $vals = array_map(function ($k) {
          return isset($k['skala']) ? (float) $k['skala'] : null;
        }, $a->kemampuan);
        $vals = array_filter($vals, function ($v) {
          return $v !== null;
        });
        if (count($vals) > 0) $score = array_sum($vals) / count($vals);
      }

      if (!isset($grouped[$pid])) {
        $grouped[$pid] = [
          'id' => $pid,
          'nama_program' => $progName,
          'kategori' => $a->program ? $a->program->kategori : null,
          'datapoints' => []
        ];
      }

      $grouped[$pid]['datapoints'][] = [
        'tanggal' => $a->tanggal_assessment ? $a->tanggal_assessment->toDateString() : ($a->created_at ? $a->created_at->toDateString() : null),
        'score' => $score !== null ? $score : null,
      ];
    }

    // Convert grouped to indexed array and sort datapoints by tanggal
    $programs = array_values($grouped);
    foreach ($programs as &$p) {
      usort($p['datapoints'], function ($x, $y) {
        if (!$x['tanggal']) return 1;
        if (!$y['tanggal']) return -1;
        return strtotime($x['tanggal']) - strtotime($y['tanggal']);
      });
    }

    return response()->json(['success' => true, 'programs' => $programs]);
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(string $id)
  {
    $assessment = Assessment::findOrFail($id);
    $anakDidiks = AnakDidik::all();
    $konsultans = Konsultan::all();

    return view('content.assessment.edit', [
      'assessment' => $assessment,
      'anakDidiks' => $anakDidiks,
      'konsultans' => $konsultans,
    ]);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {
    $assessment = Assessment::findOrFail($id);

    $validated = $request->validate([
      'anak_didik_id' => 'required|exists:anak_didiks,id',
      'konsultan_id' => 'required|exists:konsultans,id',
      'kategori' => 'required|in:bina_diri,akademik,motorik,perilaku,vokasi',
      'hasil_penilaian' => 'nullable|string',
      'rekomendasi' => 'nullable|string',
      'saran' => 'nullable|string',
      'tanggal_assessment' => 'nullable|date',
    ]);

    $assessment->update($validated);

    return redirect()->route('assessment.index')->with('success', 'Penilaian berhasil diperbarui');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    $assessment = Assessment::findOrFail($id);
    $assessment->delete();

    return response()->json([
      'success' => true,
      'message' => 'Penilaian berhasil dihapus'
    ]);
  }
}
