<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ppi;
use App\Models\PpiItem;
use App\Models\AnakDidik;
use App\Models\Konsultan;
use App\Models\Karyawan;
use App\Models\ProgramKonsultan;
use App\Models\ProgramPendidikan;
use App\Models\Assessment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\ActivityService;
use Carbon\Carbon;

class PPIController extends Controller
{
  public function index(Request $request)
  {
    $search = $request->input('search');
    $guru_fokus = $request->input('guru_fokus');

    $user = Auth::user();
    $isGuru = $user && $user->role === 'guru';
    $karyawanId = null;
    if ($isGuru) {
      $karyawan = Karyawan::where('nama', $user->name)->first();
      $karyawanId = $karyawan ? $karyawan->id : null;
    }

    // Always list all AnakDidik (allow viewing the table), access to riwayat controlled via $accessMap
    $anakQuery = AnakDidik::with('guruFokus')
      ->when($search, function ($q, $s) {
        $q->where('nama', 'like', "%{$s}%")->orWhere('nis', 'like', "%{$s}%");
      })
      ->when($guru_fokus, function ($q, $gf) {
        // filter by guru fokus id when provided
        $q->where('guru_fokus_id', $gf);
      });

    // Determine which AnakDidik are accessible by current user so we can order them first
    $isAdmin = $user && $user->role === 'admin';
    $isKonsultanPendidikan = false;
    if ($user && $user->role === 'konsultan') {
      $k = Konsultan::where('user_id', $user->id)->orWhere('email', $user->email)->first();
      if ($k && strtolower(trim($k->spesialisasi ?? '')) === 'pendidikan') $isKonsultanPendidikan = true;
    }

    $allAnakForAccess = $anakQuery->get();
    $accessibleIds = [];
    foreach ($allAnakForAccess as $a) {
      $hasAccess = false;
      if ($isAdmin) {
        $hasAccess = true;
      }
      if (!$hasAccess && $isKonsultanPendidikan) {
        $hasAccess = true;
      }
      if (!$hasAccess && $user && $user->role === 'guru') {
        if (isset($karyawanId) && $karyawanId && $a->guru_fokus_id == $karyawanId) {
          $hasAccess = true;
        } else {
          $can = \App\Http\Controllers\GuruAnakDidikController::canAccessChild($user->id, $a->id);
          if ($can) $hasAccess = true;
        }
      }
      if ($hasAccess) $accessibleIds[] = $a->id;
    }

    // order query so accessible children appear first, then by name
    if (!empty($accessibleIds)) {
      $idsCsv = implode(',', array_map('intval', $accessibleIds));
      $anakQuery = $anakQuery->orderByRaw("CASE WHEN id IN ($idsCsv) THEN 0 ELSE 1 END")->orderBy('nama');
    } else {
      $anakQuery = $anakQuery->orderBy('nama');
    }

    $anakList = $anakQuery->paginate(10)->appends($request->query());

    // (status removed) no longer building latest status per anak

    // isFokusMap: whether current user is guru_fokus for each anak
    $isFokusMap = [];
    foreach ($anakList as $a) {
      $isFokusMap[$a->id] = ($user && $user->role === 'guru' && isset($karyawanId) && $karyawanId && $a->guru_fokus_id == $karyawanId) ? true : false;
    }

    // build access map: whether current user can view riwayat for each anak
    $accessMap = [];
    foreach ($anakList as $a) {
      $hasAccess = false;
      // Admin can always view
      if ($user && $user->role === 'admin') {
        $hasAccess = true;
      }
      // Konsultan pendidikan can view
      if (!$hasAccess && $user && $user->role === 'konsultan') {
        $k = Konsultan::where('user_id', $user->id)->orWhere('email', $user->email)->first();
        if ($k && strtolower(trim($k->spesialisasi ?? '')) === 'pendidikan') $hasAccess = true;
      }
      // For guru: allow if guru_fokus OR if they have an approved assignment/request
      if (!$hasAccess && $user && $user->role === 'guru') {
        // guru fokus
        if (isset($karyawanId) && $karyawanId && $a->guru_fokus_id == $karyawanId) {
          $hasAccess = true;
        } else {
          // check GuruAnakDidik assignment or approved request
          $can = \App\Http\Controllers\GuruAnakDidikController::canAccessChild($user->id, $a->id);
          if ($can) $hasAccess = true;
        }
      }
      $accessMap[$a->id] = $hasAccess;
    }



    // build guru fokus options for the filter: only karyawan who are users with role 'guru'
    $guruOptions = Karyawan::where(function ($q) {
      $q->whereExists(function ($q2) {
        $q2->select(DB::raw(1))
          ->from('users')
          ->whereColumn('users.email', 'karyawans.email')
          ->where('users.role', 'guru');
      })->orWhereExists(function ($q2) {
        // fallback: match by name if email not available
        $q2->select(DB::raw(1))
          ->from('users')
          ->whereColumn('users.name', 'karyawans.nama')
          ->where('users.role', 'guru');
      });
    })->orderBy('nama')->get();
    $canApprovePPI = ($user && ($user->role === 'admin')) ? true : false;

    // determine if current user is a konsultan spesialisasi pendidikan
    $isKonsultanPendidikan = false;
    if ($user && $user->role === 'konsultan') {
      $k = Konsultan::where('user_id', $user->id)->orWhere('email', $user->email)->first();
      if ($k && strtolower(trim($k->spesialisasi ?? '')) === 'pendidikan') {
        $isKonsultanPendidikan = true;
      }
    }
    // build expiry map for temporary approvals (approved within last 600 minutes)
    $expiryMap = [];
    foreach ($anakList as $a) {
      $expiryMap[$a->id] = null;
      if ($user && $user->role === 'guru') {
        $approval = \App\Models\GuruAnakDidikApproval::where('requester_user_id', $user->id)
          ->where('anak_didik_id', $a->id)
          ->where('status', 'approved')
          ->whereNotNull('approved_at')
          ->where('approved_at', '>=', now()->subMinutes(600))
          ->orderByDesc('approved_at')
          ->first();
        if ($approval && $approval->approved_at) {
          $expiryMap[$a->id] = $approval->approved_at->copy()->addMinutes(600)->format('Y-m-d H:i');
        }
      }
    }

    return view('content.ppi.index', compact('anakList', 'search', 'guruOptions', 'guru_fokus', 'accessMap', 'canApprovePPI', 'isKonsultanPendidikan', 'isFokusMap', 'expiryMap'));
  }

  public function create()
  {
    $user = Auth::user();
    $anakQuery = AnakDidik::orderBy('nama');
    // if current user is a guru, only show anak didik where they are guru_fokus
    if ($user && $user->role === 'guru') {
      $karyawan = Karyawan::where('nama', $user->name)->first();
      $karyawanId = $karyawan ? $karyawan->id : null;
      if ($karyawanId) {
        $anakQuery = $anakQuery->where('guru_fokus_id', $karyawanId);
      } else {
        // no matching karyawan record found; return empty collection
        $anakQuery = $anakQuery->whereRaw('0 = 1');
      }
    }
    $anakDidiks = $anakQuery->get();
    $konsultans = Konsultan::orderBy('nama')->get();
    return view('content.ppi.create', compact('anakDidiks', 'konsultans'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'anak_didik_id' => 'required|exists:anak_didiks,id',
      'periode_mulai' => 'required|date',
      'periode_selesai' => 'required|date|after_or_equal:periode_mulai',
      'keterangan' => 'nullable|string',
      'program_items' => 'required|array|min:1'
    ]);

    $ppi = Ppi::create([
      'anak_didik_id' => $request->input('anak_didik_id'),
      'periode_mulai' => $request->input('periode_mulai'),
      'periode_selesai' => $request->input('periode_selesai'),
      'keterangan' => $request->input('keterangan'),
      'created_by' => auth()->check() ? auth()->id() : null,
    ]);

    $items = $request->input('program_items', []);
    foreach ($items as $it) {
      $nama = $it['nama_program'] ?? null;
      if (!$nama) continue;
      $programKonsultanId = $it['program_konsultan_id'] ?? null;
      if ($programKonsultanId && !\App\Models\ProgramKonsultan::find($programKonsultanId)) {
        $programKonsultanId = null;
      }
      PpiItem::create([
        'ppi_id' => $ppi->id,
        'nama_program' => $nama,
        'kategori' => $it['kategori'] ?? null,
        'program_konsultan_id' => $programKonsultanId,
      ]);
    }

    // Log aktivitas ketika user dengan role 'guru' menambahkan PPI
    $user = Auth::user();
    if ($user && $user->role === 'guru') {
      $anak = AnakDidik::find($ppi->anak_didik_id);
      $desc = 'Membuat PPI untuk anak: ' . ($anak ? $anak->nama : 'ID ' . $ppi->anak_didik_id);
      ActivityService::logCreate('PPI', $ppi->id, $desc);
    }

    return redirect()->route('ppi.index')->with('success', 'PPI berhasil disimpan');
  }

  public function show($id)
  {
    $ppi = Ppi::with('items')->findOrFail($id);
    return view('content.ppi.show', compact('ppi'));
  }

  /**
   * Return JSON riwayat PPI for a given anak didik id
   */
  public function riwayat($anakId)
  {
    $user = Auth::user();

    // Authorization: admin always, konsultan pendidikan allowed, guru allowed if fokus or assigned/approved
    $allowed = false;
    if ($user && $user->role === 'admin') {
      $allowed = true;
    } elseif ($user && $user->role === 'konsultan') {
      $k = Konsultan::where('user_id', $user->id)->orWhere('email', $user->email)->first();
      if ($k && strtolower(trim($k->spesialisasi ?? '')) === 'pendidikan') {
        $allowed = true;
      }
    } elseif ($user && $user->role === 'guru') {
      $can = \App\Http\Controllers\GuruAnakDidikController::canAccessChild($user->id, $anakId);
      if ($can) $allowed = true;
      // also allow if guru is guru_fokus
      $k = Karyawan::where('nama', $user->name)->first();
      $karyawanId = $k ? $k->id : null;
      if ($karyawanId) {
        $anak = AnakDidik::find($anakId);
        if ($anak && $anak->guru_fokus_id == $karyawanId) $allowed = true;
      }
    }

    if (!$allowed) {
      return response()->json(['success' => false, 'message' => 'Anda tidak memiliki akses'], 403);
    }

    $ppis = Ppi::with('items')->where('anak_didik_id', $anakId)->orderByDesc('created_at')->get();
    $riwayat = $ppis->map(function ($p) {
      // choose a representative item for preview: prefer Akademik category when present
      $displayItem = null;
      if ($p->items && $p->items->count()) {
        foreach ($p->items as $it) {
          if ($it->kategori && strtolower(trim($it->kategori)) === 'akademik') {
            $displayItem = $it;
            break;
          }
        }
        if (!$displayItem) $displayItem = $p->items->first();
      }

      return [
        'id' => $p->id,
        'nama_program' => $p->keterangan ? ($p->keterangan) : ($displayItem->nama_program ?? ''),
        'kategori' => $displayItem->kategori ?? '',
        'created_at' => $p->created_at ? $p->created_at->toDateTimeString() : '',
        'periode_mulai' => $p->periode_mulai ? $p->periode_mulai : null,
        'periode_selesai' => $p->periode_selesai ? $p->periode_selesai : null,
        'keterangan' => $p->keterangan ?? null,
        'items' => $p->items->map(function ($it) {
          return [
            'id' => $it->id,
            'nama_program' => $it->nama_program,
            'kategori' => $it->kategori,
            'aktif' => $it->aktif ?? 0
          ];
        })->toArray(),
        'status' => $p->status ?? null,
      ];
    });

    return response()->json(['success' => true, 'riwayat' => $riwayat]);
  }

  public function detailJson($id)
  {
    $ppi = Ppi::with(['items.programKonsultan', 'anak'])
      ->findOrFail($id);

    $data = [
      'id' => $ppi->id,
      'tanggal' => $ppi->created_at->format('Y-m-d'),
      'anak' => $ppi->anak ? ['id' => $ppi->anak->id, 'nama' => $ppi->anak->nama] : null,
      'konsultan' => null,
      'keterangan' => $ppi->keterangan ?? null,
      'status' => $ppi->status,
      'review_comment' => $ppi->review_comment ?? null,
      'items' => []
    ];

    foreach ($ppi->items as $item) {
      $pk = $item->programKonsultan;
      $data['items'][] = [
        'id' => $item->id,
        'notes' => $item->notes ?? null,
        'nama_program' => $item->nama_program ?? null,
        'kategori' => $item->kategori ?? null,
        'aktif' => $item->aktif ?? 0,
        'program_konsultan' => $pk ? [
          'id' => $pk->id,
          'kode_program' => $pk->kode_program ?? null,
          'nama_program' => $pk->nama_program ?? null,
          'tujuan' => $pk->tujuan ?? null,
          'aktivitas' => $pk->aktivitas ?? null,
          'keterangan' => $pk->keterangan ?? null,
        ] : null,
      ];
    }

    return response()->json($data);
  }

  /**
   * Set 'aktif' flag on a PpiItem (admin only)
   */
  public function setItemAktif(Request $request, $id)
  {
    $user = Auth::user();
    if (!$user || $user->role !== 'admin') {
      return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    $ppiItem = PpiItem::find($id);
    if (!$ppiItem) {
      return response()->json(['success' => false, 'message' => 'Item tidak ditemukan'], 404);
    }

    $aktif = $request->input('aktif');
    // accept various forms: true/false, 1/0, '1'/'0'
    $set = ($aktif === true || $aktif === '1' || $aktif === 1 || $aktif === 'true' || $aktif === 'on') ? 1 : 0;

    try {
      $ppiItem->aktif = $set;
      $ppiItem->save();
      return response()->json(['success' => true, 'message' => 'Status program diperbarui', 'aktif' => $set]);
    } catch (\Exception $e) {
      return response()->json(['success' => false, 'message' => 'Gagal menyimpan perubahan'], 500);
    }
  }



  /**
   * Delete a PPI. Only allowed for admin or the child's guru fokus.
   */
  public function destroy($id)
  {
    $ppi = Ppi::with('items')->findOrFail($id);
    $user = Auth::user();

    // resolve anak and its guru_fokus
    $anak = \App\Models\AnakDidik::find($ppi->anak_didik_id);

    $allowed = false;
    if ($user && $user->role === 'admin') {
      $allowed = true;
    } elseif ($user && $user->role === 'guru') {
      // map user -> karyawan
      $k = Karyawan::where('nama', $user->name)->first();
      $karyawanId = $k ? $k->id : null;
      if ($karyawanId && $anak && $anak->guru_fokus_id == $karyawanId) {
        $allowed = true;
      }
    }

    if (!$allowed) {
      return response()->json(['success' => false, 'message' => 'Anda tidak berhak menghapus PPI ini'], 403);
    }

    // delete items then ppi
    try {
      foreach ($ppi->items as $it) {
        $it->delete();
      }
      $ppi->delete();

      // Log aktivitas ketika user dengan role 'guru' menghapus PPI
      $user = Auth::user();
      if ($user && $user->role === 'guru') {
        $anak = AnakDidik::find($ppi->anak_didik_id);
        $desc = 'Menghapus PPI untuk anak: ' . ($anak ? $anak->nama : 'ID ' . $ppi->anak_didik_id);
        ActivityService::logDelete('PPI', $ppi->id, $desc);
      }

      return response()->json(['success' => true, 'message' => 'PPI berhasil dihapus']);
    } catch (\Exception $e) {
      return response()->json(['success' => false, 'message' => 'Gagal menghapus PPI'], 500);
    }
  }

  /**
   * Update PPI items (only admin or guru_fokus allowed)
   */
  public function update(Request $request, $id)
  {
    $ppi = Ppi::with('items')->findOrFail($id);
    $user = Auth::user();

    // authorization: admin or guru_fokus
    $allowed = false;
    if ($user && $user->role === 'admin') $allowed = true;
    elseif ($user && $user->role === 'guru') {
      $k = Karyawan::where('nama', $user->name)->first();
      $karyawanId = $k ? $k->id : null;
      $anak = AnakDidik::find($ppi->anak_didik_id);
      if ($karyawanId && $anak && $anak->guru_fokus_id == $karyawanId) $allowed = true;
    }

    if (!$allowed) {
      return response()->json(['success' => false, 'message' => 'Anda tidak berhak mengedit PPI ini'], 403);
    }

    $data = $request->all();
    $items = isset($data['program_items']) && is_array($data['program_items']) ? $data['program_items'] : [];

    // basic validation: each item must have nama_program
    $validItems = [];
    foreach ($items as $it) {
      $name = isset($it['nama_program']) ? trim($it['nama_program']) : '';
      if ($name === '') continue;
      $validItems[] = [
        'nama_program' => $name,
        'kategori' => $it['kategori'] ?? null,
        'program_konsultan_id' => $it['program_konsultan_id'] ?? null,
      ];
    }

    // replace items in transaction

    try {
      \DB::beginTransaction();
      // delete existing
      foreach ($ppi->items as $it) $it->delete();
      // recreate
      foreach ($validItems as $vi) {
        PpiItem::create([
          'ppi_id' => $ppi->id,
          'nama_program' => $vi['nama_program'],
          'kategori' => $vi['kategori'],
          'program_konsultan_id' => $vi['program_konsultan_id'] ?? null,
        ]);
      }
      \DB::commit();

      // Log aktivitas ketika user dengan role 'guru' mengedit PPI
      $user = Auth::user();
      if ($user && $user->role === 'guru') {
        $anak = AnakDidik::find($ppi->anak_didik_id);
        $desc = 'Mengupdate PPI untuk anak: ' . ($anak ? $anak->nama : 'ID ' . $ppi->anak_didik_id);
        ActivityService::logUpdate('PPI', $ppi->id, $desc);
      }

      return response()->json(['success' => true, 'message' => 'PPI berhasil diperbarui']);
    } catch (\Exception $e) {
      \DB::rollBack();
      return response()->json(['success' => false, 'message' => 'Gagal menyimpan perubahan'], 500);
    }
  }

  public function exportPdf(Request $request)
  {
    $request->validate([
      'anak_didik_id' => 'required|exists:anak_didiks,id',
      'periode_awal' => 'required|date_format:Y-m',
      'periode_akhir' => 'required|date_format:Y-m',
    ]);

    $anakDidikId = $request->input('anak_didik_id');
    $periodeAwalInput = $request->input('periode_awal'); // format: 2026-01
    $periodeAkhirInput = $request->input('periode_akhir'); // format: 2026-02

    // Get anak didik
    $anakDidik = AnakDidik::with('guruFokus')->findOrFail($anakDidikId);

    // Calculate period start and end dates
    $periodeAwal = Carbon::createFromFormat('Y-m', $periodeAwalInput)->startOfMonth();
    $periodeAkhir = Carbon::createFromFormat('Y-m', $periodeAkhirInput)->endOfMonth();

    // Get all PPI records for this anak didik that overlap with the selected period
    $ppiRecords = Ppi::where('anak_didik_id', $anakDidikId)
      ->where(function ($q) use ($periodeAwal, $periodeAkhir) {
        // PPI overlaps if: periode_mulai <= periodeAkhir AND periode_selesai >= periodeAwal
        $q->where('periode_mulai', '<=', $periodeAkhir)
          ->where('periode_selesai', '>=', $periodeAwal);
      })
      ->orderBy('periode_mulai')
      ->get();

    // Collect all PPI items with their program konsultan details
    $programData = [];

    foreach ($ppiRecords as $ppi) {
      $items = PpiItem::where('ppi_id', $ppi->id)
        ->with(['programKonsultan.konsultan'])
        ->get();

      foreach ($items as $item) {
        $programKonsultan = $item->programKonsultan;

        // Prepare display fields with sensible defaults
        $dispKonsultanNama = '-';
        $dispKonsultanSpec = '-';
        $dispDeskripsi = '-';
        $dispTujuan = '-';
        $dispMetode = '-';

        // If linked ProgramKonsultan exists, use its data first
        if ($programKonsultan) {
          if ($programKonsultan->konsultan) {
            $dispKonsultanNama = $programKonsultan->konsultan->nama ?? '-';
            $dispKonsultanSpec = $programKonsultan->konsultan->spesialisasi ?? '-';
          }
          $dispDeskripsi = $programKonsultan->keterangan ?? $programKonsultan->deskripsi ?? $programKonsultan->aktivitas ?? '-';
          $dispTujuan = $programKonsultan->tujuan ?? '-';
          $dispMetode = $programKonsultan->aktivitas ?? '-';
        } else {
          // Fallback: try resolve from ProgramPendidikan / ProgramKonsultan by kode/nama
          $kode = null;
          $nm = (string)($item->nama_program ?? '');
          if (preg_match('/^([A-Za-z]{3})\s*-?\s*(\d{3})/i', $nm, $m)) {
            $kode = strtoupper($m[1] . $m[2]);
          }
          // 1) Try ProgramPendidikan for this child
          $pp = ProgramPendidikan::with('konsultan')
            ->where('anak_didik_id', $anakDidikId)
            ->where(function ($q) use ($kode, $nm) {
              if ($kode) {
                $q->orWhereRaw("REPLACE(UPPER(kode_program),'-','') = ?", [str_replace('-', '', strtoupper($kode))]);
              }
              // sanitize name (remove spaces, dots, hyphens)
              $san = strtolower(preg_replace('/[\s\.-]+/', '', $nm));
              if ($san !== '') {
                $q->orWhereRaw("REPLACE(REPLACE(REPLACE(LOWER(nama_program),' ',''),'.',''),'-','') = ?", [$san]);
              }
            })
            ->orderByDesc('id')
            ->first();

          if ($pp) {
            if ($pp->konsultan) {
              $dispKonsultanNama = $pp->konsultan->nama ?? '-';
              $dispKonsultanSpec = $pp->konsultan->spesialisasi ?? '-';
            }
            $dispDeskripsi = $pp->keterangan ?? '-';
            $dispTujuan = $pp->tujuan ?? '-';
            $dispMetode = $pp->aktivitas ?? '-';
          } else if ($kode) {
            // 2) Try master ProgramKonsultan by kode (e.g., PENxxx)
            $pk = ProgramKonsultan::with('konsultan')
              ->whereRaw("REPLACE(UPPER(kode_program),'-','') = ?", [str_replace('-', '', strtoupper($kode))])
              ->first();
            if ($pk) {
              if ($pk->konsultan) {
                $dispKonsultanNama = $pk->konsultan->nama ?? '-';
                $dispKonsultanSpec = $pk->konsultan->spesialisasi ?? '-';
              }
              $dispDeskripsi = $pk->keterangan ?? $pk->deskripsi ?? $pk->aktivitas ?? $dispDeskripsi;
              $dispTujuan = $pk->tujuan ?? $dispTujuan;
              $dispMetode = $pk->aktivitas ?? $dispMetode;
            }
          }
          // 3) As a last resort: set konsultan to Pendidikan if kode starts with PEN
          if ($dispKonsultanNama === '-' && $kode && str_starts_with($kode, 'PEN')) {
            $edu = \App\Models\Konsultan::whereRaw('LOWER(spesialisasi) like ?', ['%pendidikan%'])->first();
            if ($edu) {
              $dispKonsultanNama = $edu->nama ?? 'Pendidikan';
              $dispKonsultanSpec = $edu->spesialisasi ?? 'Pendidikan';
            } else {
              $dispKonsultanNama = 'Pendidikan';
              $dispKonsultanSpec = 'Pendidikan';
            }
          }
        }

        // Get latest assessment for this program within the period (kept for potential future use)
        $maxPenilaian = null;
        
        if ($programKonsultan && $programKonsultan->konsultan_id) {
          // Query assessments by matching konsultan_id
          // Check both tanggal_assessment (if set) and created_at (fallback)
          $assessments = Assessment::where('anak_didik_id', $anakDidikId)
            ->where('konsultan_id', $programKonsultan->konsultan_id)
            ->where(function ($q) use ($periodeAwal, $periodeAkhir) {
              // Include if tanggal_assessment is within range
              $q->whereBetween('tanggal_assessment', [$periodeAwal, $periodeAkhir])
                // OR if tanggal_assessment is null, use created_at within range
                ->orWhere(function ($q2) use ($periodeAwal, $periodeAkhir) {
                  $q2->whereNull('tanggal_assessment')
                    ->whereBetween('created_at', [$periodeAwal, $periodeAkhir]);
                });
            })
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();
          
          foreach ($assessments as $assessment) {
            // Extract numeric value from hasil_penilaian using regex
            $penilaian = null;
            $hasilPenilaian = $assessment->hasil_penilaian ?? '';
            
            // Try to extract first number from the string (e.g., "4", "Skor: 4", etc.)
            if (preg_match('/\d+/', (string)$hasilPenilaian, $matches)) {
              $penilaian = (int)$matches[0];
            } elseif (is_numeric($hasilPenilaian)) {
              $penilaian = (int)$hasilPenilaian;
            }
            
            if ($penilaian !== null && ($maxPenilaian === null || $penilaian > $maxPenilaian)) {
              $maxPenilaian = $penilaian;
            }
          }
        }

        $programData[] = [
          'nama_program' => $item->nama_program,
          'kategori' => $item->kategori,
          'aktif' => $item->aktif,
          'konsultan_nama' => $dispKonsultanNama,
          'konsultan_spesialisasi' => $dispKonsultanSpec,
          'deskripsi' => $dispDeskripsi,
          'tujuan' => $dispTujuan,
          'metode' => $dispMetode,
          'durasi' => '-', // Field durasi tidak ada di tabel
          'periode_ppi_mulai' => $ppi->periode_mulai,
          'periode_ppi_selesai' => $ppi->periode_selesai,
          'max_penilaian' => $maxPenilaian,
        ];
      }
    }

    // Group by nama_program to avoid duplicates
    $programDataGrouped = collect($programData)->groupBy('nama_program')->map(function ($group) {
      return $group->first(); // Take first occurrence
    })->values()->all();

    $data = [
      'anakDidik' => $anakDidik,
      'periodeAwal' => $periodeAwal,
      'periodeAkhir' => $periodeAkhir,
      'periodeBulan' => $periodeAwal->locale('id')->isoFormat('MMMM Y') . ' - ' . $periodeAkhir->locale('id')->isoFormat('MMMM Y'),
      'programData' => $programDataGrouped,
      'tanggalCetak' => Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y'),
    ];

    return view('content.ppi.export-pdf', $data);
  }

  // additional methods (riwayat, approve) can be added later
}
