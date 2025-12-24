<?php

namespace App\Http\Controllers;

use App\Models\Konsultan;
use App\Services\ActivityService;
use Illuminate\Http\Request;

class KonsultanDataController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $query = Konsultan::query();

    // Search by nama or nik or email
    if ($request->filled('search')) {
      $search = $request->search;
      $query->where(function ($q) use ($search) {
        $q->where('nama', 'like', "%{$search}%")
          ->orWhere('nik', 'like', "%{$search}%")
          ->orWhere('email', 'like', "%{$search}%");
      });
    }

    // Filter by jenis_kelamin
    if ($request->filled('jenis_kelamin')) {
      $query->where('jenis_kelamin', $request->jenis_kelamin);
    }

    // Filter by spesialisasi
    if ($request->filled('spesialisasi')) {
      $query->where('spesialisasi', $request->spesialisasi);
    }

    // Filter by status_hubungan
    if ($request->filled('status_hubungan')) {
      $query->where('status_hubungan', $request->status_hubungan);
    }

    $query->orderBy('nama', 'asc');
    $konsultans = $query->paginate(15)->appends($request->query());

    // Get unique values for filter dropdowns
    $spesialisasiOptions = Konsultan::whereNotNull('spesialisasi')
      ->distinct()
      ->pluck('spesialisasi')
      ->sort()
      ->values();

    $data = [
      'title' => 'Konsultan',
      'konsultans' => $konsultans,
      'spesialisasiOptions' => $spesialisasiOptions
    ];
    return view('content.konsultan.index', $data);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    return view('content.konsultan.create');
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'nama' => 'required|string|max:255',
      'nik' => 'nullable|string|unique:konsultans',
      'jenis_kelamin' => 'nullable|in:laki-laki,perempuan',
      'tanggal_lahir' => 'nullable|date',
      'tempat_lahir' => 'nullable|string',
      'alamat' => 'nullable|string',
      'no_telepon' => 'nullable|string',
      'email' => 'nullable|email|unique:konsultans',
      'spesialisasi' => 'nullable|string',
      'bidang_keahlian' => 'nullable|string',
      'sertifikasi' => 'nullable|string',
      'pengalaman_tahun' => 'nullable|integer',
      'status_hubungan' => 'nullable|in:aktif,non-aktif',
      'tanggal_registrasi' => 'nullable|date',
      'pendidikan_terakhir' => 'nullable|string',
      'institusi_pendidikan' => 'nullable|string',
    ]);

    Konsultan::create($validated);

    // Log activity
    ActivityService::logCreate('Konsultan', null, 'Membuat data konsultan baru');

    return redirect()->route('konsultan.index')
      ->with('success', 'Konsultan berhasil ditambahkan');
  }

  /**
   * Display the specified resource.
   */
  public function show(Request $request, Konsultan $konsultan)
  {
    // If AJAX request, return JSON
    if ($request->wantsJson() || $request->ajax()) {
      return response()->json([
        'success' => true,
        'data' => $konsultan
      ]);
    }

    // Otherwise return view
    return view('content.konsultan.show', ['konsultan' => $konsultan]);
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Konsultan $konsultan)
  {
    return view('content.konsultan.edit', ['konsultan' => $konsultan]);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Konsultan $konsultan)
  {
    $validated = $request->validate([
      'nama' => 'required|string|max:255',
      'nik' => 'nullable|string|unique:konsultans,nik,' . $konsultan->id,
      'jenis_kelamin' => 'nullable|in:laki-laki,perempuan',
      'tanggal_lahir' => 'nullable|date',
      'tempat_lahir' => 'nullable|string',
      'alamat' => 'nullable|string',
      'no_telepon' => 'nullable|string',
      'email' => 'nullable|email|unique:konsultans,email,' . $konsultan->id,
      'spesialisasi' => 'nullable|string',
      'bidang_keahlian' => 'nullable|string',
      'sertifikasi' => 'nullable|string',
      'pengalaman_tahun' => 'nullable|integer',
      'status_hubungan' => 'nullable|in:aktif,non-aktif',
      'tanggal_registrasi' => 'nullable|date',
      'pendidikan_terakhir' => 'nullable|string',
      'institusi_pendidikan' => 'nullable|string',
    ]);

    $konsultan->update($validated);

    // Log activity
    ActivityService::logUpdate('Konsultan', $konsultan->id, 'Mengupdate data konsultan: ' . $konsultan->nama);

    return redirect()->route('konsultan.index')
      ->with('success', 'Data konsultan berhasil diperbarui');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Konsultan $konsultan)
  {
    $namaKonsultan = $konsultan->nama;
    $konsultan->delete();

    // Log activity
    ActivityService::logDelete('Konsultan', $konsultan->id, 'Menghapus data konsultan: ' . $namaKonsultan);

    // If request is AJAX / expects JSON, return JSON response
    if (request()->wantsJson() || request()->ajax()) {
      return response()->json([
        'success' => true,
        'message' => 'Data konsultan berhasil dihapus'
      ]);
    }

    return redirect()->route('konsultan.index')
      ->with('success', 'Data konsultan berhasil dihapus');
  }
}
