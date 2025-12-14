<?php

namespace App\Http\Controllers;

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
    $query = Program::with('anakDidik.guruFokus', 'konsultan');
    if ($user->role === 'konsultan') {
      // Filter hanya program yang dibuat oleh konsultan ini
      $konsultanId = Konsultan::where('user_id', $user->id)->value('id');
      $query->where('konsultan_id', $konsultanId);
    }
    if ($request->filled('search')) {
      $search = $request->search;
      $query->where(function ($q) use ($search) {
        $q->where('nama_program', 'like', "%{$search}%")
          ->orWhereHas('anakDidik', function ($q) use ($search) {
            $q->where('nama', 'like', "%{$search}%");
          });
      });
    }
    if ($request->filled('is_approved')) {
      $query->where('is_approved', $request->is_approved === 'true');
    }
    // Guru Fokus filter
    if ($request->filled('guru_fokus')) {
      $guruFokusId = $request->guru_fokus;
      $query->whereHas('anakDidik', function ($q) use ($guruFokusId) {
        $q->where('guru_fokus_id', $guruFokusId);
      });
    }
    if ($request->filled('kategori')) {
      $query->where('kategori', $request->kategori);
    }
    $programs = $query->paginate(15)->appends($request->query());
    // Guru Fokus options (Karyawan dengan posisi Guru Fokus saja)
    $guruOptions = \App\Models\Karyawan::where('posisi', 'Guru Fokus')->orderBy('nama')->pluck('nama', 'id');
    $data = [
      'title' => 'Program Pembelajaran',
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
    $validated = $request->validate($rules);

    $data = $validated;
    if ($isKonsultan) {
      // Set konsultan_id otomatis dari user login
      $konsultan = \App\Models\Konsultan::where('user_id', $user->id)->first();
      $data['konsultan_id'] = $konsultan ? $konsultan->id : null;
      $data['nama_program'] = '';
      $data['kategori'] = null;
    }
    if ($request->has('kemampuan')) {
      $data['kemampuan'] = array_values($request->input('kemampuan'));
    }
    $data['wawancara'] = $request->input('wawancara');
    $data['kemampuan_saat_ini'] = $request->input('kemampuan_saat_ini');
    $data['saran_rekomendasi'] = $request->input('saran_rekomendasi');

    Program::create($data);

    // Log activity
    ActivityService::logCreate('Program', null, 'Membuat program pembelajaran baru');

    return redirect()->route('program.index')->with('success', 'Program pembelajaran berhasil ditambahkan');
  }

  /**
   * Display the specified resource.
   */
  public function show(string $id)
  {
    $program = Program::with(['anakDidik.guruFokus', 'konsultan'])->findOrFail($id);
    // Format data agar JS bisa langsung pakai
    $data = $program->toArray();
    // Format tanggal
    $data['tanggal_mulai'] = $program->tanggal_mulai ? $program->tanggal_mulai->format('Y-m-d') : null;
    $data['tanggal_selesai'] = $program->tanggal_selesai ? $program->tanggal_selesai->format('Y-m-d') : null;
    // Sertakan nama guru fokus jika ada
    $data['anak_didik']['guru_fokus_nama'] = $program->anakDidik && $program->anakDidik->guruFokus ? $program->anakDidik->guruFokus->nama : null;
    return response()->json([
      'success' => true,
      'data' => $data
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
