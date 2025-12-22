<?php

namespace App\Http\Controllers;

use App\Models\AnakDidik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Konsultan;
use App\Models\Karyawan;
use App\Http\Controllers\GuruAnakDidikController;

class PPIController extends Controller
{
  public function index(Request $request)
  {
    $user = Auth::user();

    // If user is konsultan but not spesialisasi Pendidikan, forbid access
    if ($user && $user->role === 'konsultan' && !$this->isKonsultanPendidikan($user)) {
      abort(403);
    }

    $query = AnakDidik::with('guruFokus')->orderBy('nama');
    if ($request->filled('search')) {
      $q = $request->search;
      $query->where(function ($s) use ($q) {
        $s->where('nama', 'like', "%{$q}%")->orWhere('nis', 'like', "%{$q}%");
      });
    }

    // Filter by guru fokus (Karyawan id)
    if ($request->filled('guru_fokus')) {
      $query->where('guru_fokus_id', $request->guru_fokus);
    }

    $perPage = 15;
    $anakPaginator = $query->paginate($perPage)->appends($request->query());

    // Build access map for items on current page only
    $accessMap = [];
    // Try to resolve current user to a Karyawan record (match by name) to cover guru_fokus mapping
    $karyawanForUser = null;
    if ($user && $user->role === 'guru') {
      $karyawanForUser = \App\Models\Karyawan::where('nama', $user->name)->first();
    }
    foreach ($anakPaginator->items() as $anak) {
      if ($user->role === 'admin') {
        $accessMap[$anak->id] = true;
        continue;
      }
      // Direct assignment or approved request
      $can = GuruAnakDidikController::canAccessChild($user->id, $anak->id);
      // Additionally, if the anak's guruFokus (Karyawan) matches this user's Karyawan record, grant access
      if (!$can && $karyawanForUser && $anak->guru_fokus_id && $karyawanForUser->id == $anak->guru_fokus_id) {
        $can = true;
      }
      $accessMap[$anak->id] = $can;
    }

    // Provide list of guru fokus options for filter dropdown
    $guruOptions = Karyawan::whereNotNull('posisi')
      ->whereRaw('LOWER(posisi) LIKE ?', ['%guru%'])
      ->orderBy('nama')
      ->get();

    $user = Auth::user();
    $canApprovePPI = $this->isKonsultanPendidikan($user);

    return view('content.ppi.index', [
      'anakList' => $anakPaginator,
      'accessMap' => $accessMap,
      'search' => $request->search ?? null,
      'guruOptions' => $guruOptions,
      'guru_fokus' => $request->guru_fokus ?? null,
      'canApprovePPI' => $canApprovePPI,
    ]);
  }

  /**
   * API: Riwayat PPI untuk anak didik tertentu
   */
  public function riwayat($anakId)
  {
    $items = \App\Models\ProgramAnak::where('anak_didik_id', $anakId)
      ->orderByDesc('created_at')
      ->get();

    $riwayat = $items->map(function ($p) {
      return [
        'id' => $p->id,
        'nama_program' => $p->nama_program,
        'periode_mulai' => $p->periode_mulai ? $p->periode_mulai->format('Y-m-d') : null,
        'periode_selesai' => $p->periode_selesai ? $p->periode_selesai->format('Y-m-d') : null,
        'status' => $p->status,
        'keterangan' => $p->keterangan ?? null,
        'created_at' => $p->created_at ? $p->created_at->format('d-m-Y H:i') : '',
      ];
    });

    return response()->json([
      'success' => true,
      'riwayat' => $riwayat,
    ]);
  }

  /**
   * Approve a PPI entry (only konsultan pendidikan allowed)
   */
  public function approve(Request $request, $id)
  {
    $user = Auth::user();
    if (!$this->isKonsultanPendidikan($user)) {
      return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    $p = \App\Models\ProgramAnak::findOrFail($id);
    $anak = $p->anakDidik;
    // Require that anak has a guru_fokus assigned
    if (!$anak || !$anak->guru_fokus_id) {
      return response()->json(['success' => false, 'message' => 'Tidak ada guru fokus untuk anak ini'], 400);
    }

    $p->status = 'disetujui';
    $p->save();

    return response()->json(['success' => true, 'message' => 'PPI berhasil disetujui']);
  }

  public function create()
  {
    $user = Auth::user();
    if ($user && $user->role === 'konsultan' && !$this->isKonsultanPendidikan($user)) {
      abort(403);
    }
    $anakDidiks = AnakDidik::orderBy('nama')->get();
    $konsultans = Konsultan::orderBy('nama')->get();
    return view('content.ppi.create', compact('anakDidiks', 'konsultans'));
  }

  public function store(Request $request)
  {
    $user = Auth::user();
    if ($user && $user->role === 'konsultan' && !$this->isKonsultanPendidikan($user)) {
      abort(403);
    }

    $validated = $request->validate([
      'anak_didik_id' => 'required|exists:anak_didiks,id',
      'nama_program' => 'required|string|max:255',
      'periode_mulai' => 'required|date',
      'periode_selesai' => 'required|date|after_or_equal:periode_mulai',
      'keterangan' => 'nullable|string',
      'rekomendasi' => 'nullable|string',
    ]);

    $validated['status'] = 'aktif';
    \App\Models\ProgramAnak::create($validated);

    return redirect()->route('ppi.index')->with('success', 'PPI berhasil ditambahkan');
  }

  public function show($id)
  {
    $user = Auth::user();
    if ($user && $user->role === 'konsultan' && !$this->isKonsultanPendidikan($user)) {
      abort(403);
    }
    $anak = AnakDidik::with('guruFokus')->findOrFail($id);

    $canAccess = false;
    if ($user->role === 'admin') {
      $canAccess = true;
    } else {
      $canAccess = GuruAnakDidikController::canAccessChild($user->id, $anak->id);
      if (!$canAccess && $user->role === 'guru') {
        $karyawan = \App\Models\Karyawan::where('nama', $user->name)->first();
        if ($karyawan && $anak->guru_fokus_id && $karyawan->id == $anak->guru_fokus_id) {
          $canAccess = true;
        }
      }
    }

    if (!$canAccess) {
      return view('content.ppi.locked', ['anak' => $anak]);
    }

    // If allowed, show program anak detail page (reuse program-anak.show if exists)
    return redirect()->route('program-anak.show', $anak->id);
  }

  /**
   * Resolve whether the given user is a Konsultan with spesialisasi Pendidikan
   */
  private function isKonsultanPendidikan($user)
  {
    if (!$user) return false;
    // Must be konsultan role
    if ($user->role !== 'konsultan') return false;
    // Try resolve Konsultan record by user_id, email, or name
    $k = Konsultan::where('user_id', $user->id)->first();
    if (!$k && $user->email) {
      $k = Konsultan::where('email', $user->email)->first();
    }
    if (!$k && $user->name) {
      $k = Konsultan::where('nama', 'like', "%{$user->name}%")->first();
    }
    if (!$k) return false;
    $sp = strtolower($k->spesialisasi ?? '');
    return $sp === 'pendidikan';
  }
}
