<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\AnakDidik;
use App\Models\Konsultan;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgramController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $query = Program::with('anakDidik', 'konsultan');

    // Filter by konsultan for non-admin
    $user = Auth::user();
    if ($user->role === 'konsultan') {
      $konsultan = Konsultan::where('user_id', $user->id)->first();
      if ($konsultan) {
        $query->where('konsultan_id', $konsultan->id);
      }
    }

    // Search by nama program atau nama anak
    if ($request->filled('search')) {
      $search = $request->search;
      $query->where(function ($q) use ($search) {
        $q->where('nama_program', 'like', "%{$search}%")
          ->orWhereHas('anakDidik', function ($q) use ($search) {
            $q->where('nama', 'like', "%{$search}%");
          });
      });
    }

    // Filter by status approval
    if ($request->filled('is_approved')) {
      $query->where('is_approved', $request->is_approved === 'true');
    }

    // Filter by kategori
    if ($request->filled('kategori')) {
      $query->where('kategori', $request->kategori);
    }

    $programs = $query->paginate(15)->appends($request->query());

    $data = [
      'title' => 'Program Pembelajaran',
      'programs' => $programs,
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

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'anak_didik_id' => 'required|exists:anak_didiks,id',
      'konsultan_id' => 'required|exists:konsultans,id',
      'nama_program' => 'required|string|max:255',
      'deskripsi' => 'nullable|string',
      'kategori' => 'required|in:bina_diri,akademik,motorik,perilaku,vokasi',
      'target_pembelajaran' => 'nullable|string',
      'tanggal_mulai' => 'nullable|date',
      'tanggal_selesai' => 'nullable|date',
      'catatan_konsultan' => 'nullable|string',
    ]);

    Program::create($validated);

    // Log activity
    ActivityService::logCreate('Program', null, 'Membuat program pembelajaran baru');

    return redirect()->route('program.index')->with('success', 'Program pembelajaran berhasil ditambahkan');
  }

  /**
   * Display the specified resource.
   */
  public function show(string $id)
  {
    $program = Program::with('anakDidik', 'konsultan')->findOrFail($id);
    return response()->json([
      'success' => true,
      'data' => $program
    ]);
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(string $id)
  {
    $program = Program::findOrFail($id);
    $anakDidiks = AnakDidik::all();
    $konsultans = Konsultan::all();

    return view('content.program.edit', [
      'program' => $program,
      'anakDidiks' => $anakDidiks,
      'konsultans' => $konsultans,
    ]);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {
    $program = Program::findOrFail($id);

    $validated = $request->validate([
      'anak_didik_id' => 'required|exists:anak_didiks,id',
      'konsultan_id' => 'required|exists:konsultans,id',
      'nama_program' => 'required|string|max:255',
      'deskripsi' => 'nullable|string',
      'kategori' => 'required|in:bina_diri,akademik,motorik,perilaku,vokasi',
      'target_pembelajaran' => 'nullable|string',
      'tanggal_mulai' => 'nullable|date',
      'tanggal_selesai' => 'nullable|date',
      'catatan_konsultan' => 'nullable|string',
      'is_approved' => 'nullable|boolean',
    ]);

    $program->update($validated);

    // Log activity
    ActivityService::logUpdate('Program', $program->id, 'Mengupdate program pembelajaran: ' . $program->nama_program);

    return redirect()->route('program.index')->with('success', 'Program pembelajaran berhasil diperbarui');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    $program = Program::findOrFail($id);
    $namaProgramPembelajaran = $program->nama_program;
    $program->delete();

    // Log activity
    ActivityService::logDelete('Program', $id, 'Menghapus program pembelajaran: ' . $namaProgramPembelajaran);

    return response()->json([
      'success' => true,
      'message' => 'Program pembelajaran berhasil dihapus'
    ]);
  }

  /**
   * Approve program (Konsultan Pendidikan Only)
   */
  public function approve(string $id)
  {
    $program = Program::findOrFail($id);
    $program->update(['is_approved' => true]);

    // Log activity
    ActivityService::logApprove('Program', $id, 'Menyetujui program pembelajaran: ' . $program->nama_program);

    return redirect()->route('program.index')->with('success', 'Program berhasil disetujui');
  }
}
