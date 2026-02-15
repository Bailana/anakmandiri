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
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use App\Services\ActivityService;

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
    // List AnakDidik (paginated). If logged in as `guru`, restrict to:
    // - anak yang memiliki guru_fokus = guru ini
    // - anak yang diberi akses via GuruAnakDidik (assigned)
    // - anak yang diberi persetujuan sementara via GuruAnakDidikApproval (approved)
    // Untuk role lain, show all anak dengan guru_fokus.
    $perPage = 10;
    if (Auth::check() && Auth::user()->role === 'guru') {
      $user = Auth::user();
      // assigned children via explicit assignment table
      $assignedIds = GuruAnakDidik::where('user_id', $user->id)->where('status', 'aktif')->pluck('anak_didik_id')->toArray();
      // temporary approvals (same logic as create view)
      $positiveStatuses = ['approved', 'accepted', 'disetujui', 'approve', 'approved_by_admin', 'accepted_by_admin'];
      $approvedIds = GuruAnakDidikApproval::where('requester_user_id', $user->id)
        ->whereIn('status', $positiveStatuses)
        ->whereNotNull('approved_at')
        ->where('approved_at', '>=', now()->subMinutes(600))
        ->pluck('anak_didik_id')
        ->toArray();

      // children where this guru is the recorded guru_fokus
      $karyawan = Karyawan::where('nama', $user->name)->first();
      $fokusIds = [];
      if ($karyawan) {
        $fokusIds = AnakDidik::where('guru_fokus_id', $karyawan->id)->pluck('id')->toArray();
      }

      $ids = array_values(array_unique(array_merge($assignedIds, $approvedIds, $fokusIds)));

      if (count($ids) > 0) {
        $query = AnakDidik::with('guruFokus')->whereIn('id', $ids)->orderBy('nama', 'asc');
        if ($request->filled('search')) {
          $search = $request->search;
          $query->where(function ($q) use ($search) {
            $q->where('nama', 'like', "%{$search}%")
              ->orWhere('nis', 'like', "%{$search}%");
          });
        }
        $paginated = $query->paginate($perPage)->appends($request->query());
      } else {
        // no children available for this guru -> empty paginator
        $paginated = new LengthAwarePaginator(collect([]), 0, $perPage, 1, ['path' => LengthAwarePaginator::resolveCurrentPath()]);
      }
    } else {
      // default behaviour for non-guru users
      $query = AnakDidik::with('guruFokus')->whereNotNull('guru_fokus_id')->orderBy('nama', 'asc');
      // Search by nama or NIS
      if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
          $q->where('nama', 'like', "%{$search}%")
            ->orWhere('nis', 'like', "%{$search}%");
        });
      }
      $paginated = $query->paginate($perPage)->appends($request->query());
    }

    // convert each AnakDidik model into an object with `anakDidik` property to keep view compatibility
    $paginated->getCollection()->transform(function ($a) {
      $obj = new \stdClass();
      $obj->id = $a->id;
      $obj->anakDidik = $a;
      return $obj;
    });

    $assessments = $paginated;

    // Prepare wajib counts: total mandatory programs per anak and how many were assessed today
    $anakIds = collect($assessments->getCollection())->pluck('anakDidik')->pluck('id')->all();
    $wajibTotals = [];
    $wajibDoneToday = [];
    foreach ($anakIds as $anakId) {
      // get active PPI items for this anak
      $items = PpiItem::whereHas('ppi', function ($q) use ($anakId) {
        $q->where('anak_didik_id', $anakId);
      })->where('aktif', 1)->get();

      $names = $items->pluck('nama_program')->map(function ($n) {
        return trim(strtolower($n ?? ''));
      })->filter()->unique()->values()->all();

      $total = count($names);
      $wajibTotals[$anakId] = $total;
      $done = 0;
      if ($total > 0) {
        // find Program ids that match these names (case-insensitive)
        $programs = Program::where(function ($q) use ($names) {
          foreach ($names as $n) {
            $q->orWhereRaw('LOWER(nama_program) = ?', [$n]);
          }
        })->pluck('id')->toArray();

        if (!empty($programs)) {
          // count unique programs assessed today (distinct program_id)
          $done = \App\Models\Assessment::where('anak_didik_id', $anakId)
            ->whereDate('tanggal_assessment', Carbon::today())
            ->whereIn('program_id', $programs)
            ->distinct('program_id')
            ->count('program_id');
        } else {
          // no matching Program records; fallback: try to count assessments whose hasil_penilaian or aktivitas mention the program name
          $doneCount = 0;
          foreach ($names as $nm) {
            $c = \App\Models\Assessment::where('anak_didik_id', $anakId)
              ->whereDate('tanggal_assessment', Carbon::today())
              ->where(function ($q) use ($nm) {
                $q->whereRaw('LOWER(COALESCE(hasil_penilaian, "")) LIKE ?', ["%{$nm}%"])
                  ->orWhereRaw('LOWER(COALESCE(aktivitas, "")) LIKE ?', ["%{$nm}%"]);
              })->count();
            if ($c) $doneCount++;
          }
          $done = $doneCount;
        }
      }
      $wajibDoneToday[$anakId] = $done;
    }

    $data = [
      'title' => 'Penilaian Anak',
      'assessments' => $assessments,
      'wajibTotals' => $wajibTotals,
      'wajibDoneToday' => $wajibDoneToday,
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
      // support multiple possible approval status values (localized or different conventions)
      $positiveStatuses = ['approved', 'accepted', 'disetujui', 'approve', 'approved_by_admin', 'accepted_by_admin'];
      // Only consider approvals that are recent (still within temporary access window)
      $approvedIds = GuruAnakDidikApproval::where('requester_user_id', $user->id)
        ->whereIn('status', $positiveStatuses)
        ->whereNotNull('approved_at')
        ->where('approved_at', '>=', now()->subMinutes(600))
        ->pluck('anak_didik_id')
        ->toArray();

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

    // Cek status absensi hari ini untuk setiap anak didik
    $absensiHariIni = [];
    $today = Carbon::today()->format('Y-m-d');

    foreach ($anakDidiks as $anak) {
      $absensi = \App\Models\Absensi::where('anak_didik_id', $anak->id)
        ->whereDate('tanggal', $today)
        ->first();

      $absensiHariIni[$anak->id] = [
        'sudah_absen' => $absensi !== null,
        'status' => $absensi ? $absensi->status : null,
        'boleh_dinilai' => $absensi && $absensi->status === 'hadir'
      ];
    }

    return view('content.assessment.create', [
      'anakDidiks' => $anakDidiks,
      'konsultans' => $konsultans,
      'absensiHariIni' => $absensiHariIni,
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
      'tanggal_assessment' => 'required|date',
      'aktivitas' => 'nullable|string',
      'hasil_penilaian' => 'nullable|string',
      'rekomendasi' => 'nullable|string',
      'saran' => 'nullable|string',
      'kemampuan' => 'nullable|array',
      'kemampuan.*.judul' => 'nullable|string',
      'kemampuan.*.skala' => 'nullable|in:1,2,3,4,5',
      'programs' => 'required|array|min:1',
      'programs.*.program_id' => 'nullable|exists:programs,id',
      'programs.*.kategori' => 'required|in:bina_diri,akademik,motorik,perilaku,vokasi',
      'programs.*.perkembangan' => 'nullable|integer|min:1|max:4',
    ]);

    // Validasi: anak didik harus sudah absensi dengan status hadir pada tanggal penilaian
    $dateToCheck = isset($validated['tanggal_assessment']) ? Carbon::parse($validated['tanggal_assessment'])->toDateString() : Carbon::today()->toDateString();
    $absensi = \App\Models\Absensi::where('anak_didik_id', $validated['anak_didik_id'])
      ->whereDate('tanggal', $dateToCheck)
      ->first();

    if (!$absensi || $absensi->status !== 'hadir') {
      return redirect()->back()->withErrors([
        'anak_didik_id' => 'Penilaian hanya dapat dilakukan untuk anak didik yang sudah absensi dengan status hadir hari ini.'
      ])->withInput();
    }

    // Ensure kemampuan is an array (store empty array if not provided)
    $validated['kemampuan'] = array_values($request->input('kemampuan', []));

    // attach current user id when available so we know who created the assessment
    $user = Auth::user();
    if ($user) {
      $validated['user_id'] = $user->id;
    }

    // Create one Assessment per program entry
    $created = [];
    $programs = $validated['programs'] ?? [];
    foreach ($programs as $p) {
      $data = [
        'anak_didik_id' => $validated['anak_didik_id'],
        'konsultan_id' => $validated['konsultan_id'] ?? null,
        'program_id' => $p['program_id'] ?? null,
        'kategori' => $p['kategori'],
        'perkembangan' => isset($p['perkembangan']) && $p['perkembangan'] !== '' ? $p['perkembangan'] : null,
        'aktivitas' => $validated['aktivitas'] ?? null,
        'hasil_penilaian' => $validated['hasil_penilaian'] ?? null,
        'rekomendasi' => $validated['rekomendasi'] ?? null,
        'saran' => $validated['saran'] ?? null,
        'tanggal_assessment' => $validated['tanggal_assessment'] ?? null,
        'kemampuan' => $validated['kemampuan'],
      ];

      if ($user) $data['user_id'] = $user->id;

      $a = Assessment::create($data);
      $created[] = $a;

      // Log aktivitas per penilaian apabila user role guru
      if ($user && $user->role === 'guru') {
        $anak = AnakDidik::find($validated['anak_didik_id']);
        $desc = 'Membuat penilaian untuk anak: ' . ($anak ? $anak->nama : 'ID ' . $validated['anak_didik_id']);
        ActivityService::logCreate('Assessment', $a->id, $desc);
      }
    }

    return redirect()->route('assessment.index')->with('success', 'Penilaian berhasil ditambahkan');
  }

  /**
   * Return PPI programs for a given anak didik and kategori (creates Program rows if missing)
   */
  public function ppiPrograms(Request $request)
  {
    $anakId = $request->query('anak_didik_id');
    $kategori = $request->query('kategori');
    $tanggalQuery = $request->query('tanggal') ?? $request->query('tanggal_assessment') ?? null;

    if (!$anakId || !$kategori) {
      return response()->json(['success' => false, 'programs' => []]);
    }

    // map frontend kategori values to stored PPI kategori labels
    $kategoriMap = [
      'bina_diri' => 'Bina Diri',
      'akademik' => 'Akademik',
      'motorik' => 'Motorik',
      'perilaku' => 'Basic Learning',
      'vokasi' => 'Vokasi',
    ];

    $searchKategori = isset($kategoriMap[$kategori]) ? $kategoriMap[$kategori] : str_replace('_', ' ', $kategori);

    // match kategori case-insensitively; allow caller to request inactive items via include_inactive
    $originalKategoriText = str_replace('_', ' ', $kategori);
    $lk = strtolower($searchKategori);
    $lorig = strtolower($originalKategoriText);

    $includeInactive = false;
    if ($request->has('include_inactive')) {
      $val = $request->query('include_inactive');
      $includeInactive = filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    }

    $itemsQuery = PpiItem::whereHas('ppi', function ($q) use ($anakId) {
      $q->where('anak_didik_id', $anakId);
    })->when(!$includeInactive, function ($q) {
      $q->where('aktif', 1);
    })->where(function ($q) use ($lk, $lorig) {
      $q->whereRaw('LOWER(kategori) = ?', [$lk])
        ->orWhereRaw('LOWER(kategori) LIKE ?', ["%{$lk}%"])
        ->orWhereRaw('LOWER(kategori) LIKE ?', ["%{$lorig}%"]);
    });

    $items = $itemsQuery->get();

    // If `programs` table exists, try to ensure a Program record; otherwise return names from PPI items
    $names = [];
    foreach ($items as $it) {
      $name = trim($it->nama_program ?? '');
      if ($name === '') continue;
      $names[$name] = $name;
    }

    // determine target date for excluding already-assessed programs
    // Only apply exclusion when caller explicitly provided a tanggal/tanggal_assessment parameter.
    $targetDate = null;
    if ($tanggalQuery) {
      try {
        $targetDate = Carbon::parse($tanggalQuery)->toDateString();
      } catch (\Throwable $e) {
        $targetDate = null;
      }
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


        // if targetDate provided, skip program if an assessment exists for this anak & program on that date
        if ($targetDate) {
          $already = Assessment::where('anak_didik_id', $anakId)
            ->where('program_id', $prog->id)
            ->whereDate('tanggal_assessment', $targetDate)
            ->exists();
          if ($already) continue;
        }

        $result[] = ['id' => $prog->id, 'nama_program' => $prog->nama_program];
      }
    } else {
      // return names with null id so frontend can display them; selection will leave program_id null
      foreach ($names as $name) {
        // for non-Program rows (id=null), try to detect if an assessment mentioning the program name
        // already exists on target date for this anak; only skip if targetDate provided
        if ($targetDate) {
          $nm = trim(strtolower($name));
          $already = Assessment::where('anak_didik_id', $anakId)
            ->whereDate('tanggal_assessment', $targetDate)
            ->where(function ($q) use ($nm) {
              $q->whereRaw('LOWER(COALESCE(hasil_penilaian, "")) LIKE ?', ["%{$nm}%"])
                ->orWhereRaw('LOWER(COALESCE(aktivitas, "")) LIKE ?', ["%{$nm}%"]);
            })->exists();
          if ($already) continue;
        }

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
    // Build per-program per-date selection: prefer latest assessment by guru_fokus (if any),
    // otherwise pick latest assessment overall for that date.
    $grouped = [];
    // determine guru_fokus user id for this anak (if available)
    $guruUserId = null;
    try {
      $anak = AnakDidik::find($anakId);
      if ($anak && $anak->guru_fokus_id) {
        $k = Karyawan::find($anak->guru_fokus_id);
        if ($k && isset($k->user_id)) $guruUserId = $k->user_id;
      }
    } catch (\Throwable $e) {
      $guruUserId = null;
    }

    // organize assessments by program id (or 'general') then by date
    $byProgDate = [];
    $progMeta = [];
    foreach ($assessments as $a) {
      $pid = $a->program_id ?? 'general';
      $progName = $a->program ? $a->program->nama_program : ($a->program_id ? ('Program #' . $a->program_id) : 'Umum');
      $dateKey = $a->tanggal_assessment ? (string)$a->tanggal_assessment->toDateString() : ($a->created_at ? (string)$a->created_at->toDateString() : null);
      if (!$dateKey) continue;
      if (!isset($byProgDate[$pid])) $byProgDate[$pid] = [];
      if (!isset($byProgDate[$pid][$dateKey])) $byProgDate[$pid][$dateKey] = [];
      $byProgDate[$pid][$dateKey][] = $a;
      if (!isset($progMeta[$pid])) {
        $progMeta[$pid] = ['nama_program' => $progName, 'kategori' => $a->program ? $a->program->kategori : null];
      }
    }

    // select chosen assessment per program per date
    foreach ($byProgDate as $pid => $dates) {
      $grouped[$pid] = [
        'id' => $pid,
        'nama_program' => $progMeta[$pid]['nama_program'] ?? null,
        'kategori' => $progMeta[$pid]['kategori'] ?? null,
        'datapoints' => []
      ];
      foreach ($dates as $dateKey => $arr) {
        // prefer assessments by guruUserId (if present)
        $chosen = null;
        if ($guruUserId) {
          $byGuru = array_filter($arr, function ($x) use ($guruUserId) {
            return isset($x->user_id) && $x->user_id == $guruUserId;
          });
          if (!empty($byGuru)) {
            usort($byGuru, function ($x, $y) {
              return strtotime($y->created_at) - strtotime($x->created_at);
            });
            $chosen = $byGuru[0];
          }
        }
        // fallback: choose latest by created_at
        if (!$chosen) {
          usort($arr, function ($x, $y) {
            return strtotime($y->created_at) - strtotime($x->created_at);
          });
          $chosen = $arr[0];
        }

        // compute score for chosen assessment
        $score = null;
        if ($chosen) {
          if (!is_null($chosen->perkembangan)) {
            $score = (float) $chosen->perkembangan;
          } elseif (is_array($chosen->kemampuan) && count($chosen->kemampuan) > 0) {
            $vals = array_map(function ($k) {
              return isset($k['skala']) ? (float) $k['skala'] : null;
            }, $chosen->kemampuan);
            $vals = array_filter($vals, function ($v) {
              return $v !== null;
            });
            if (count($vals) > 0) $score = array_sum($vals) / count($vals);
          }
        }

        $grouped[$pid]['datapoints'][] = [
          'tanggal' => $dateKey,
          'score' => $score !== null ? $score : null,
        ];
      }
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
