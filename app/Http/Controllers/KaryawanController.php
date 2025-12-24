<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Services\ActivityService;
use Illuminate\Http\Request;

class KaryawanController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $statusOptions = ['tetap', 'kontrak', 'honorer'];
    $query = Karyawan::query();

    // Search by nama or nip or email
    if ($request->filled('search')) {
      $search = $request->search;
      $query->where(function ($q) use ($search) {
        $q->where('nama', 'like', "%{$search}%")
          ->orWhere('nip', 'like', "%{$search}%")
          ->orWhere('email', 'like', "%{$search}%");
      });
    }

    // Filter by jenis_kelamin
    // Filter by posisi
    if ($request->filled('posisi')) {
      $query->where('posisi', $request->posisi);
    }

    // Filter by departemen
    if ($request->filled('departemen')) {
      $query->where('departemen', $request->departemen);
    }

    // Filter by status_kepegawaian
    if ($request->filled('status_kepegawaian')) {
      $query->where('status_kepegawaian', $request->status_kepegawaian);
    }

    $query->orderBy('nama', 'asc');
    $karyawans = $query->paginate(15)->appends($request->query());

    // Get unique values for filter dropdowns
    $departemenOptions = Karyawan::whereNotNull('departemen')
      ->distinct()
      ->pluck('departemen')
      ->sort()
      ->values();

    return view('content.karyawan.index', [
      'title' => 'Karyawan',
      'karyawans' => $karyawans,
      'departemenOptions' => $departemenOptions,
      'statusOptions' => $statusOptions,
      'posisiOptions' => Karyawan::query()->distinct()->pluck('posisi')->filter()->values(),
    ]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    return view('content.karyawan.create');
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'nama' => 'required|string|max:255',
      'nik' => 'nullable|string|unique:karyawans',
      'nip' => 'nullable|string|unique:karyawans',
      'jenis_kelamin' => 'nullable|in:laki-laki,perempuan',
      'tanggal_lahir' => 'nullable|date',
      'tempat_lahir' => 'nullable|string',
      'alamat' => 'nullable|string',
      'no_telepon' => 'nullable|string',
      'email' => 'nullable|email|unique:karyawans',
      'posisi' => 'nullable|string',
      'departemen' => 'nullable|string',
      'status_kepegawaian' => 'nullable|in:Tetap,Training',
      'tanggal_bergabung' => 'nullable|date',
      'pendidikan_terakhir' => 'nullable|string',
      'institusi_pendidikan' => 'nullable|string',
      'keahlian' => 'nullable|string',
    ]);

    Karyawan::create($validated);

    // Log activity
    ActivityService::logCreate('Karyawan', null, 'Membuat data karyawan baru');

    return redirect()->route('karyawan.index')
      ->with('success', 'Karyawan berhasil ditambahkan');
  }

  /**
   * Display the specified resource.
   */
  public function show(Request $request, Karyawan $karyawan)
  {
    // If AJAX request, return JSON
    if ($request->wantsJson() || $request->ajax()) {
      return response()->json([
        'success' => true,
        'data' => $karyawan
      ]);
    }

    // Otherwise return view
    return view('content.karyawan.show', ['karyawan' => $karyawan]);
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Karyawan $karyawan)
  {
    return view('content.karyawan.edit', ['karyawan' => $karyawan]);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Karyawan $karyawan)
  {
    $validated = $request->validate([
      'nama' => 'required|string|max:255',
      'nik' => 'nullable|string|unique:karyawans,nik,' . $karyawan->id,
      'nip' => 'nullable|string|unique:karyawans,nip,' . $karyawan->id,
      'jenis_kelamin' => 'nullable|in:laki-laki,perempuan',
      'tanggal_lahir' => 'nullable|date',
      'tempat_lahir' => 'nullable|string',
      'alamat' => 'nullable|string',
      'no_telepon' => 'nullable|string',
      'email' => 'nullable|email|unique:karyawans,email,' . $karyawan->id,
      'posisi' => 'nullable|string',
      'departemen' => 'nullable|string',
      'status_kepegawaian' => 'nullable|in:tetap,kontrak,honorer',
      'tanggal_bergabung' => 'nullable|date',
      'pendidikan_terakhir' => 'nullable|string',
      'institusi_pendidikan' => 'nullable|string',
      'keahlian' => 'nullable|string',
    ]);

    $karyawan->update($validated);

    // Log activity
    ActivityService::logUpdate('Karyawan', $karyawan->id, 'Mengupdate data karyawan: ' . $karyawan->nama);

    return redirect()->route('karyawan.index')
      ->with('success', 'Data karyawan berhasil diperbarui');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Karyawan $karyawan)
  {
    $namaKaryawan = $karyawan->nama;
    $karyawan->delete();

    // Log activity
    ActivityService::logDelete('Karyawan', $karyawan->id, 'Menghapus data karyawan: ' . $namaKaryawan);

    if (request()->wantsJson() || request()->ajax()) {
      return response()->json(['success' => true, 'message' => 'Data karyawan berhasil dihapus']);
    }
    return redirect()->route('karyawan.index')
      ->with('success', 'Data karyawan berhasil dihapus');
  }
}
