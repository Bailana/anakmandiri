<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AnakDidik;
use App\Models\Konsultan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssessmentController extends Controller
{
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
    $anakDidiks = AnakDidik::all();
    $konsultans = Konsultan::all();

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
    ]);

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
