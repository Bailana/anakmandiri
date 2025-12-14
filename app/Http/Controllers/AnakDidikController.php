<?php

namespace App\Http\Controllers;

use App\Models\AnakDidik;
use App\Services\ActivityService;
use Illuminate\Http\Request;

class AnakDidikController extends Controller
{
  public function __construct()
  {
    $this->middleware('role:admin')->only(['create', 'store', 'edit', 'update', 'destroy']);
  }
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $query = AnakDidik::with('guruAssignments.user');

    // Search by nama or nis
    if ($request->filled('search')) {
      $search = $request->search;
      $query->where(function ($q) use ($search) {
        $q->where('nama', 'like', "%{$search}%")
          ->orWhere('nis', 'like', "%{$search}%");
      });
    }

    // Filter by jenis_kelamin
    if ($request->filled('jenis_kelamin')) {
      $query->where('jenis_kelamin', $request->jenis_kelamin);
    }

    // Filter by guru fokus (menggunakan field guru_fokus_id pada tabel anak didik)
    if ($request->filled('guru_fokus')) {
      $query->where('guru_fokus_id', $request->guru_fokus);
    }

    $anakDidiks = $query->paginate(15)->appends($request->query());

    // Get unique values for filter dropdowns
    $guruOptions = \App\Models\User::where('role', 'guru')
      ->orderBy('name')
      ->pluck('name', 'id');

    $data = [
      'title' => 'Anak Didik',
      'anakDidiks' => $anakDidiks,
      'guruOptions' => $guruOptions
    ];
    return view('content.anak-didik.index', $data);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    return view('content.anak-didik.create');
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'nama' => 'required|string|max:255',
      'nis' => 'required|string|unique:anak_didiks',
      'jenis_kelamin' => 'required|in:laki-laki,perempuan',
      'tanggal_lahir' => 'nullable|date',
      'tempat_lahir' => 'nullable|string',
      'alamat' => 'nullable|string',
      'no_telepon' => 'nullable|string',
      'email' => 'nullable|email',
      'nama_orang_tua' => 'nullable|string',
      'no_telepon_orang_tua' => 'nullable|string',
      'no_kk' => 'nullable|string',
      'nik' => 'nullable|string',
      'no_akta_kelahiran' => 'nullable|string',
      'tinggi_badan' => 'nullable|numeric',
      'berat_badan' => 'nullable|numeric',
      'jumlah_saudara_kandung' => 'nullable|integer',
      'anak_ke' => 'nullable|integer',
      'tinggal_bersama' => 'nullable|string',
      'pendidikan_terakhir' => 'nullable|string',
      'asal_sekolah' => 'nullable|string',
      'tanggal_pendaftaran' => 'nullable|date',
      'kk' => 'nullable|boolean',
      'ktp_orang_tua' => 'nullable|boolean',
      'akta_kelahiran' => 'nullable|boolean',
      'foto_anak' => 'nullable|boolean',
      'pemeriksaan_tes_rambut' => 'nullable|boolean',
      'anamnesa' => 'nullable|boolean',
      'tes_iq' => 'nullable|boolean',
      'pemeriksaan_dokter_lab' => 'nullable|boolean',
      'surat_pernyataan' => 'nullable|boolean',
    ]);

    $anakDidik = AnakDidik::create($validated);

    // Log activity
    ActivityService::logCreate('AnakDidik', $anakDidik->id, 'Membuat data anak didik: ' . $anakDidik->nama);

    return redirect()->route('anak-didik.index')->with('success', 'Data anak didik berhasil ditambahkan');
  }

  /**
   * Display the specified resource.
   */
  public function show(string $id)
  {
    $anakDidik = AnakDidik::findOrFail($id);
    return response()->json([
      'success' => true,
      'data' => $anakDidik
    ]);
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(string $id)
  {
    $anakDidik = AnakDidik::findOrFail($id);
    // Ambil semua guru fokus (karyawan dengan posisi Guru Fokus)
    $guruFokusList = \App\Models\Karyawan::where('posisi', 'Guru Fokus')->orderBy('nama')->pluck('nama', 'id');
    return view('content.anak-didik.edit', [
      'anakDidik' => $anakDidik,
      'guruFokusList' => $guruFokusList
    ]);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {
    $anakDidik = AnakDidik::findOrFail($id);

    $validated = $request->validate([
      'guru_fokus_id' => 'required|exists:karyawans,id',
      'nama' => 'required|string|max:255',
      'nis' => 'required|string|unique:anak_didiks,nis,' . $id,
      'jenis_kelamin' => 'required|in:laki-laki,perempuan',
      'tanggal_lahir' => 'nullable|date',
      'tempat_lahir' => 'nullable|string',
      'alamat' => 'nullable|string',
      'no_telepon' => 'nullable|string',
      'email' => 'nullable|email',
      'nama_orang_tua' => 'nullable|string',
      'no_telepon_orang_tua' => 'nullable|string',
      'no_kk' => 'nullable|string',
      'nik' => 'nullable|string',
      'no_akta_kelahiran' => 'nullable|string',
      'tinggi_badan' => 'nullable|numeric',
      'berat_badan' => 'nullable|numeric',
      'jumlah_saudara_kandung' => 'nullable|integer',
      'anak_ke' => 'nullable|integer',
      'tinggal_bersama' => 'nullable|string',
      'pendidikan_terakhir' => 'nullable|string',
      'asal_sekolah' => 'nullable|string',
      'tanggal_pendaftaran' => 'nullable|date',
      'kk' => 'nullable|boolean',
      'ktp_orang_tua' => 'nullable|boolean',
      'akta_kelahiran' => 'nullable|boolean',
      'foto_anak' => 'nullable|boolean',
      'pemeriksaan_tes_rambut' => 'nullable|boolean',
      'anamnesa' => 'nullable|boolean',
      'tes_iq' => 'nullable|boolean',
      'pemeriksaan_dokter_lab' => 'nullable|boolean',
      'surat_pernyataan' => 'nullable|boolean',
    ]);

    $anakDidik->update($validated);

    // Log activity
    ActivityService::logUpdate('AnakDidik', $anakDidik->id, 'Mengupdate data anak didik: ' . $anakDidik->nama);

    return redirect()->route('anak-didik.index')->with('success', 'Data anak didik berhasil diperbarui');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    $anakDidik = AnakDidik::findOrFail($id);
    $namaAnakDidik = $anakDidik->nama;
    $anakDidik->delete();

    // Log activity
    ActivityService::logDelete('AnakDidik', $id, 'Menghapus data anak didik: ' . $namaAnakDidik);

    return response()->json([
      'success' => true,
      'message' => 'Data anak didik berhasil dihapus'
    ]);
  }

  /**
   * Export anak didik data to PDF
   */
  public function exportPdf(string $id)
  {
    $anakDidik = AnakDidik::with(['assessments', 'therapyPrograms', 'programs'])->findOrFail($id);

    return view('content.anak-didik.pdf', ['anakDidik' => $anakDidik]);
  }
}
