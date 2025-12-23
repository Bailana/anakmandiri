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
      'kemampuan' => 'required|array',
      'kemampuan.*.judul' => 'required|string',
      'kemampuan.*.skala' => 'required|in:1,2,3,4,5',
    ]);

    $validated['kemampuan'] = array_values($request->input('kemampuan', []));

    Assessment::create($validated);

    return redirect()->route('assessment.index')->with('success', 'Penilaian berhasil ditambahkan');
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
