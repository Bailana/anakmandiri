<?php

namespace App\Http\Controllers;

use App\Models\ProgramAnak;
use App\Models\AnakDidik;
use App\Models\ProgramKonsultan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProgramAnakController extends Controller
{
  public function index()
  {
    // show one row per anak didik (latest program per anak)
    $latestIds = ProgramAnak::groupBy('anak_didik_id')->selectRaw('MAX(id) as id')->pluck('id')->toArray();
    // order by anak didik name A-Z so listings are alphabetical by child
    $query = ProgramAnak::with(['anakDidik', 'programKonsultan.konsultan'])
      ->whereIn('program_anak.id', $latestIds)
      ->join('anak_didiks', 'program_anak.anak_didik_id', '=', 'anak_didiks.id')
      ->select('program_anak.*')
      ->orderBy('anak_didiks.nama', 'asc');
    if (request('search')) {
      $search = request('search');
      $query->where(function ($q) use ($search) {
        $q->whereHas('anakDidik', function ($q2) use ($search) {
          $q2->where('nama', 'like', "%$search%");
        })
          ->orWhere('nama_program', 'like', "%$search%");
      });
    }
    // filter by periode range (periode_start and/or periode_end in format YYYY-MM)
    if (request('periode_start') || request('periode_end')) {
      try {
        if (request('periode_start')) {
          $start = Carbon::createFromFormat('Y-m', request('periode_start'))->startOfMonth()->toDateString();
          // keep programs that end on/after selected start
          $query->where('periode_selesai', '>=', $start);
        }
        if (request('periode_end')) {
          $end = Carbon::createFromFormat('Y-m', request('periode_end'))->endOfMonth()->toDateString();
          // keep programs that start on/before selected end
          $query->where('periode_mulai', '<=', $end);
        }
      } catch (\Exception $e) {
        // ignore parse errors and do not apply periode filter
      }
    }
    // use pagination so the view can call paginator methods (currentPage, perPage, firstItem, links, etc.)
    $programAnak = $query->paginate(10)->withQueryString();
    $currentKonsultanSpesRaw = null;
    $currentKonsultanId = null;
    if (auth()->check() && auth()->user()->role === 'konsultan') {
      $user = auth()->user();
      $k = \App\Models\Konsultan::where('user_id', $user->id)->value('spesialisasi');
      $currentKonsultanId = \App\Models\Konsultan::where('user_id', $user->id)->value('id');
      if (!$k && $user->email) {
        $k = \App\Models\Konsultan::where('email', $user->email)->value('spesialisasi');
        if (!$currentKonsultanId) $currentKonsultanId = \App\Models\Konsultan::where('email', $user->email)->value('id');
      }
      if (!$k) {
        $k = \App\Models\Konsultan::where('nama', $user->name)->value('spesialisasi');
        if (!$currentKonsultanId) $currentKonsultanId = \App\Models\Konsultan::where('nama', $user->name)->value('id');
      }
      $currentKonsultanSpesRaw = $k;
    }
    return view('content.program-anak.index', compact('programAnak', 'currentKonsultanSpesRaw', 'currentKonsultanId'));
  }

  /**
   * Return JSON list of programs assigned to an anak didik, grouped by konsultan/user.
   */
  public function riwayatProgram($anakDidikId)
  {
    $items = ProgramAnak::with(['programKonsultan.konsultan', 'programKonsultan'])
      ->where('anak_didik_id', $anakDidikId)
      ->orderByDesc('created_at')
      ->get();

    $groups = [];
    foreach ($items as $it) {
      $konsultan = $it->programKonsultan ? $it->programKonsultan->konsultan : null;
      $key = $konsultan ? 'konsultan_' . $konsultan->id : 'user_' . ($it->created_by ?? 'system');
      $name = $konsultan ? ($konsultan->nama ?? '-') : ($it->created_by_name ?? '-');
      $spes = $konsultan ? ($konsultan->spesialisasi ?? null) : null;
      if (!isset($groups[$key])) $groups[$key] = ['name' => $name, 'konsultan_id' => ($konsultan ? $konsultan->id : null), 'spesialisasi' => $spes, 'items' => []];
      $groups[$key]['items'][] = [
        'id' => $it->id,
        'kode_program' => $it->kode_program,
        'nama_program' => $it->nama_program,
        'tujuan' => $it->tujuan,
        'aktivitas' => $it->aktivitas,
        'program_konsultan_id' => $it->program_konsultan_id,
        'created_at' => $it->created_at ? $it->created_at->toDateString() : null,
        'is_suggested' => $it->is_suggested ? 1 : 0,
        'konsultan_spesialisasi' => $spes,
        'keterangan' => $it->keterangan ?? null,
        'created_by' => $it->created_by ?? null,
      ];
    }

    return response()->json(['success' => true, 'riwayat' => array_values($groups)]);
  }

  /**
   * Return JSON list of programs for an anak didik filtered by konsultan id
   */
  public function riwayatProgramByKonsultan($anakDidikId, $konsultanId)
  {
    $items = ProgramAnak::with('programKonsultan.konsultan')
      ->where('anak_didik_id', $anakDidikId)
      ->whereHas('programKonsultan', function ($q) use ($konsultanId) {
        $q->where('konsultan_id', $konsultanId);
      })
      ->orderByDesc('created_at')
      ->get();

    $out = [];
    foreach ($items as $it) {
      $out[] = [
        'id' => $it->id,
        'kode_program' => $it->kode_program,
        'nama_program' => $it->nama_program,
        'tujuan' => $it->tujuan,
        'aktivitas' => $it->aktivitas,
        'created_at' => $it->created_at ? $it->created_at->toDateString() : null,
        'is_suggested' => $it->is_suggested ? 1 : 0,
        'konsultan' => ($it->programKonsultan && $it->programKonsultan->konsultan) ? ['id' => $it->programKonsultan->konsultan->id, 'nama' => $it->programKonsultan->konsultan->nama, 'spesialisasi' => $it->programKonsultan->konsultan->spesialisasi ?? null] : null,
        'keterangan' => $it->keterangan ?? null,
      ];
    }

    return response()->json(['success' => true, 'programs' => $out]);
  }

  /**
   * Return programs for an anak by konsultan and specific date (YYYY-MM-DD)
   */
  public function riwayatProgramByKonsultanAndDate($anakDidikId, $konsultanId, $date)
  {
    // ensure date is in YYYY-MM-DD format (basic check)
    try {
      $dt = \Carbon\Carbon::createFromFormat('Y-m-d', $date);
      $dateOnly = $dt->toDateString();
    } catch (\Exception $e) {
      return response()->json(['success' => false, 'message' => 'Invalid date format']);
    }

    $items = ProgramAnak::with(['programKonsultan.konsultan'])
      ->where('anak_didik_id', $anakDidikId)
      ->whereHas('programKonsultan', function ($q) use ($konsultanId) {
        $q->where('konsultan_id', $konsultanId);
      })
      ->whereDate('created_at', $dateOnly)
      ->orderByDesc('created_at')
      ->get();

    $out = [];
    foreach ($items as $it) {
      $out[] = [
        'id' => $it->id,
        'kode_program' => $it->kode_program,
        'nama_program' => $it->nama_program,
        'tujuan' => $it->tujuan,
        'aktivitas' => $it->aktivitas,
        'periode_mulai' => $it->periode_mulai ? $it->periode_mulai->toDateString() : null,
        'periode_selesai' => $it->periode_selesai ? $it->periode_selesai->toDateString() : null,
        'konsultan' => $it->programKonsultan && $it->programKonsultan->konsultan ? ['id' => $it->programKonsultan->konsultan->id, 'nama' => $it->programKonsultan->konsultan->nama, 'spesialisasi' => $it->programKonsultan->konsultan->spesialisasi ?? null] : null,
        'is_suggested' => $it->is_suggested ? 1 : 0,
        'created_at' => $it->created_at ? $it->created_at->toDateTimeString() : null,
        'keterangan' => $it->keterangan ?? null,
      ];
    }

    return response()->json(['success' => true, 'programs' => $out]);
  }

  /**
   * Return list of ProgramKonsultan for a konsultan as JSON
   */
  public function listProgramKonsultan($konsultanId)
  {
    $items = ProgramKonsultan::where('konsultan_id', $konsultanId)->orderBy('kode_program')->get();
    $out = [];
    foreach ($items as $it) {
      $out[] = [
        'id' => $it->id,
        'kode_program' => $it->kode_program,
        'nama_program' => $it->nama_program,
        'tujuan' => $it->tujuan,
        'aktivitas' => $it->aktivitas,
      ];
    }
    return response()->json(['success' => true, 'program_konsultan' => $out]);
  }

  /**
   * Set or unset is_suggested for all programs for an anak+konsultan on a given date.
   * Expects JSON body: { suggest: 1|0 }
   */
  public function setSuggestForGroup(Request $request, $anakDidikId, $konsultanId, $date)
  {
    // authorization: only admin or the konsultan owner may toggle suggestions for this konsultan
    $user = auth()->user();
    if (!$user) {
      return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    }
    if ($user->role !== 'admin') {
      if ($user->role !== 'konsultan') {
        return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
      }
      // verify konsultan ownership: the konsultan record must map to this user (fallbacks similar to other methods)
      $konsultanRecId = \App\Models\Konsultan::where('user_id', $user->id)->value('id');
      if (!$konsultanRecId && $user->email) {
        $konsultanRecId = \App\Models\Konsultan::where('email', $user->email)->value('id');
      }
      if (!$konsultanRecId) {
        $konsultanRecId = \App\Models\Konsultan::where('nama', $user->name)->value('id');
      }
      if (!$konsultanRecId || intval($konsultanRecId) !== intval($konsultanId)) {
        return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
      }
    }
    try {
      $dt = Carbon::createFromFormat('Y-m-d', $date);
      $dateOnly = $dt->toDateString();
    } catch (\Exception $e) {
      return response()->json(['success' => false, 'message' => 'Invalid date format'], 400);
    }

    $suggest = $request->input('suggest');
    $suggestFlag = ($suggest == 1 || $suggest === true || $suggest === '1') ? 1 : 0;

    // update ProgramAnak rows that belong to this anak and whose ProgramKonsultan maps to the konsultan
    $updated = ProgramAnak::where('anak_didik_id', $anakDidikId)
      ->whereHas('programKonsultan', function ($q) use ($konsultanId) {
        $q->where('konsultan_id', $konsultanId);
      })
      ->whereDate('created_at', $dateOnly)
      ->update(['is_suggested' => $suggestFlag]);

    return response()->json(['success' => true, 'message' => 'Suggestion updated', 'updated' => $updated, 'suggest' => $suggestFlag]);
  }

  public function create()
  {
    $anakDidiks = AnakDidik::all();
    $konsultans = \App\Models\Konsultan::all();
    // load program master grouped by konsultan for populating dropdowns in the form
    $programMasters = ProgramKonsultan::all()->groupBy('konsultan_id');
    $currentKonsultanId = null;
    $currentKonsultanSpesRaw = null;
    if (auth()->check() && auth()->user()->role === 'konsultan') {
      $user = auth()->user();
      $k = \App\Models\Konsultan::where('user_id', $user->id)->value('spesialisasi');
      $currentKonsultanId = \App\Models\Konsultan::where('user_id', $user->id)->value('id');
      if (!$k && $user->email) {
        $k = \App\Models\Konsultan::where('email', $user->email)->value('spesialisasi');
        if (!$currentKonsultanId) $currentKonsultanId = \App\Models\Konsultan::where('email', $user->email)->value('id');
      }
      if (!$k) {
        $k = \App\Models\Konsultan::where('nama', $user->name)->value('spesialisasi');
        if (!$currentKonsultanId) $currentKonsultanId = \App\Models\Konsultan::where('nama', $user->name)->value('id');
      }
      $currentKonsultanSpesRaw = $k;
    }
    return view('content.program-anak.create', compact('anakDidiks', 'konsultans', 'programMasters', 'currentKonsultanId', 'currentKonsultanSpesRaw'));
  }

  public function storeProgramKonsultan(Request $request)
  {
    $request->validate([
      'kode_program' => 'nullable|string|max:100',
      'nama_program' => 'required|string|max:255',
      'tujuan' => 'nullable|string',
      'aktivitas' => 'nullable|string',
      'keterangan' => 'nullable|string',
    ]);

    // normalize kode_program: remove non-alphanumeric and uppercase (no hyphens)
    $rawKode = $request->input('kode_program');
    $kodeSanitized = $rawKode ? strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $rawKode)) : null;

    // determine konsultan_id from current user (if any)
    $konsultanId = null;
    $user = auth()->user();
    if ($user) {
      $konsultanId = \App\Models\Konsultan::where('user_id', $user->id)->value('id');
      if (!$konsultanId && $user->email) {
        $konsultanId = \App\Models\Konsultan::where('email', $user->email)->value('id');
      }
      if (!$konsultanId) {
        $konsultanId = \App\Models\Konsultan::where('nama', $user->name)->value('id');
      }
    }

    ProgramKonsultan::create([
      'konsultan_id' => $konsultanId,
      'kode_program' => $kodeSanitized,
      'nama_program' => $request->input('nama_program'),
      'tujuan' => $request->input('tujuan'),
      'aktivitas' => $request->input('aktivitas'),
      'keterangan' => $request->input('keterangan'),
    ]);

    return redirect()->back()->with('success', 'Daftar program berhasil ditambahkan');
  }

  public function daftarProgramKonsultan()
  {
    $query = ProgramKonsultan::with('konsultan');
    if (auth()->check() && auth()->user()->role === 'konsultan') {
      $user = auth()->user();
      $spec = \App\Models\Konsultan::where('user_id', $user->id)->value('spesialisasi');
      if (!$spec && $user->email) {
        $spec = \App\Models\Konsultan::where('email', $user->email)->value('spesialisasi');
      }
      if (!$spec) {
        $spec = \App\Models\Konsultan::where('nama', $user->name)->value('spesialisasi');
      }
      if ($spec) {
        $query->whereHas('konsultan', function ($q) use ($spec) {
          $q->where('spesialisasi', 'like', "%$spec%");
        });
      }
    }

    // apply search filter across kode_program, nama_program, tujuan, aktivitas
    if (request('search')) {
      $s = request('search');
      $query->where(function ($q) use ($s) {
        $q->where('kode_program', 'like', "%$s%")
          ->orWhere('nama_program', 'like', "%$s%")
          ->orWhere('tujuan', 'like', "%$s%")
          ->orWhere('aktivitas', 'like', "%$s%");
      });
    }

    // admin-only: filter by konsultan spesialisasi if provided (e.g., Psikologi, Pendidikan, Wicara, Sensori Integrasi)
    if (request()->filled('filter_konsultan')) {
      $filter = request('filter_konsultan');
      $query->whereHas('konsultan', function ($q) use ($filter) {
        $q->where('spesialisasi', 'like', "%{$filter}%");
      });
    }

    // order by kode_program prefix then numeric suffix (natural ordering like WIC001, WIC002)
    // Use MySQL 8+ regex functions if available: order by prefix (non-numeric part) then numeric suffix cast to integer
    try {
      // strip non-alphanumeric characters when ordering (so SI-001 and SI001 are treated same)
      $programs = $query->orderByRaw("REGEXP_REPLACE(REGEXP_REPLACE(kode_program, '[^A-Za-z0-9]', ''), '\\\\d+$', '') ASC")
        ->orderByRaw("CAST(REGEXP_SUBSTR(REGEXP_REPLACE(kode_program, '[^0-9]', ''), '\\\\d+$') AS UNSIGNED) ASC")
        ->paginate(10)->withQueryString();
    } catch (\Exception $e) {
      // fallback to ordering by sanitized kode_program string if regex functions not available
      $programs = $query->orderByRaw("REPLACE(REPLACE(REPLACE(kode_program,'-',''),' ',''),'.','') ASC")->paginate(10)->withQueryString();
    }

    // determine prefix based on konsultan spesialisasi (if available)
    $prefix = null;
    if (isset($spec) && $spec) {
      $s = strtolower($spec);
      if (str_contains($s, 'wicara') || str_contains($s, 'wic')) {
        $prefix = 'WIC';
      } elseif (str_contains($s, 'sensori') || str_contains($s, 'integrasi')) {
        $prefix = 'SI';
      } elseif (str_contains($s, 'psikologi') || str_contains($s, 'psiko')) {
        $prefix = 'PS';
      } else {
        // take first 3 letters of spesialisasi alphanumeric
        $clean = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($spec));
        $prefix = substr($clean, 0, 3) ?: 'PRG';
      }
    }

    // find last kode matching prefix (if prefix available) or any last kode
    if ($prefix) {
      // match regardless of non-alphanumeric separators by stripping them in DB like query
      $lastKode = ProgramKonsultan::whereRaw("REPLACE(REPLACE(REPLACE(kode_program,'-',''),' ','') ,'.','') LIKE ?", [$prefix . '%'])->orderByDesc('id')->value('kode_program');
    } else {
      $lastKode = ProgramKonsultan::whereNotNull('kode_program')->orderByDesc('id')->value('kode_program');
    }

    $nextKode = null;
    if ($lastKode) {
      if (preg_match('/^(.*?)(\d+)$/', $lastKode, $m)) {
        $lastPrefix = preg_replace('/[^A-Za-z0-9]/', '', $m[1]);
        $num = intval($m[2]) + 1;
        $digits = strlen($m[2]);
        // if we derived a prefix, ensure we use it; otherwise use sanitized lastPrefix
        $usePrefix = $prefix ?? $lastPrefix;
        $usePrefix = preg_replace('/[^A-Za-z0-9]/', '', $usePrefix);
        $nextKode = $usePrefix . str_pad($num, $digits, '0', STR_PAD_LEFT);
      } else {
        $usePrefix = preg_replace('/[^A-Za-z0-9]/', '', ($prefix ?? $lastKode));
        $nextKode = $usePrefix . '001';
      }
    } else {
      $nextKode = preg_replace('/[^A-Za-z0-9]/', '', ($prefix ?? 'PRG')) . '001';
    }

    return view('content.program-anak.daftar-program', compact('programs', 'nextKode'));
  }

  public function updateProgramKonsultan(Request $request, $id)
  {
    $request->validate([
      'kode_program' => 'nullable|string|max:100',
      'nama_program' => 'required|string|max:255',
      'tujuan' => 'nullable|string',
      'aktivitas' => 'nullable|string',
      'keterangan' => 'nullable|string',
    ]);

    $program = ProgramKonsultan::findOrFail($id);
    // normalize kode_program before update
    $rawKode = $request->input('kode_program', $program->kode_program);
    $kodeSanitized = $rawKode ? strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $rawKode)) : null;
    $program->update([
      // keep konsultan_id unchanged to avoid FK changes
      'kode_program' => $kodeSanitized,
      'nama_program' => $request->input('nama_program'),
      'tujuan' => $request->input('tujuan'),
      'aktivitas' => $request->input('aktivitas'),
      'keterangan' => $request->input('keterangan'),
    ]);

    // refresh model to ensure latest values
    $program->refresh();

    if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
      return response()->json(['success' => true, 'message' => 'Daftar program berhasil diupdate', 'program' => $program]);
    }

    return redirect()->route('program-anak.daftar-program')->with('success', 'Daftar program berhasil diupdate');
  }

  public function destroyProgramKonsultan($id)
  {
    $program = ProgramKonsultan::findOrFail($id);
    $program->delete();
    return redirect()->route('program-anak.daftar-program')->with('success', 'Daftar program berhasil dihapus');
  }

  public function store(Request $request)
  {
    // Basic common validation
    $request->validate([
      'konsultan_id' => 'required|exists:konsultans,id',
      'anak_didik_id' => 'required|exists:anak_didiks,id',
      'periode_mulai' => 'required|date',
      'periode_selesai' => 'required|date|after_or_equal:periode_mulai',
      'status' => 'nullable|in:aktif,selesai,nonaktif',
      'keterangan' => 'nullable|string',
    ]);

    // resolve konsultan spesialisasi
    $konsultan = null;
    try {
      $konsultan = \App\Models\Konsultan::find($request->input('konsultan_id'));
    } catch (\Exception $e) {
      $konsultan = null;
    }
    $konsultanSpes = $konsultan ? strtolower($konsultan->spesialisasi ?? '') : '';

    // If konsultan is psikologi, require rekomendasi and save a single ProgramAnak record
    if ($konsultanSpes && strpos($konsultanSpes, 'psikologi') !== false) {
      $request->validate([
        'rekomendasi' => 'required|string',
      ]);

      // create one ProgramAnak entry representing the psikologi recommendation
      // program_konsultan_id must be NULL here (this FK references program_konsultan.id,
      // not konsultans.id); setting konsultan id here caused FK constraint error.
      // attach creator info so riwayat can display konsultan/user name
      $creatorId = auth()->check() ? auth()->id() : null;
      $creatorName = null;
      if (auth()->check()) {
        $creatorName = auth()->user()->name ?? null;
      }
      $created = ProgramAnak::create([
        'anak_didik_id' => $request->input('anak_didik_id'),
        'program_konsultan_id' => null,
        'kode_program' => null,
        'nama_program' => 'Rekomendasi Psikologi',
        'tujuan' => null,
        'aktivitas' => null,
        'periode_mulai' => $request->input('periode_mulai'),
        'periode_selesai' => $request->input('periode_selesai'),
        'status' => $request->input('status', 'aktif'),
        'keterangan' => $request->input('keterangan'),
        'rekomendasi' => $request->input('rekomendasi'),
        'created_by' => $creatorId,
        'created_by_name' => $creatorName,
        'is_suggested' => $request->input('is_suggested') ? 1 : 0,
      ]);
      // audit: record create
      try {
        \DB::table('program_anak_audits')->insert([
          'program_anak_id' => $created->id,
          'action' => 'create',
          'user_id' => auth()->check() ? auth()->id() : null,
          'user_name' => auth()->check() ? (auth()->user()->name ?? null) : null,
          'changes' => json_encode($created->toArray()),
          'created_at' => now(),
          'updated_at' => now(),
        ]);
      } catch (\Exception $e) {
      }

      return redirect()->route('program-anak.index')->with('success', 'Rekomendasi psikologi berhasil disimpan');
    }

    // Default flow for non-psikologi konsultan: program_items required
    $request->validate([
      'program_items' => 'required|array|min:1',
    ]);

    $items = $request->input('program_items', []);

    \DB::transaction(function () use ($items, $request, $konsultanSpes) {
      foreach ($items as $it) {
        // basic sanitation / mapping
        $nama = $it['nama_program'] ?? null;
        if (!$nama) continue; // skip empty rows

        // try to resolve program_konsultan_id: prefer provided id, fallback to lookup by kode_program
        $programKonsultanId = $it['program_konsultan_id'] ?? null;
        if (empty($programKonsultanId) && !empty($it['kode_program'])) {
          $rawKode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $it['kode_program']));
          $programKonsultanId = ProgramKonsultan::whereRaw("REPLACE(REPLACE(REPLACE(UPPER(kode_program),'-',''),' ',''),'.','') = ?", [$rawKode])->value('id');
        }

        $creatorId = auth()->check() ? auth()->id() : null;
        $creatorName = auth()->check() ? (auth()->user()->name ?? null) : null;
        $programAnak = ProgramAnak::create([
          'anak_didik_id' => $request->input('anak_didik_id'),
          'program_konsultan_id' => $programKonsultanId,
          'kode_program' => $it['kode_program'] ?? null,
          'nama_program' => $nama,
          'tujuan' => $it['tujuan'] ?? null,
          'aktivitas' => $it['aktivitas'] ?? null,
          'periode_mulai' => $request->input('periode_mulai'),
          'periode_selesai' => $request->input('periode_selesai'),
          'status' => $request->input('status', 'aktif'),
          'keterangan' => $request->input('keterangan'),
          'rekomendasi' => $request->input('rekomendasi'),
          'created_by' => $creatorId,
          'created_by_name' => $creatorName,
          'is_suggested' => $request->input('is_suggested') ? 1 : 0,
        ]);

        // audit: record create for each program item
        try {
          \DB::table('program_anak_audits')->insert([
            'program_anak_id' => $programAnak->id,
            'action' => 'create',
            'user_id' => auth()->check() ? auth()->id() : null,
            'user_name' => auth()->check() ? (auth()->user()->name ?? null) : null,
            'changes' => json_encode($programAnak->toArray()),
            'created_at' => now(),
            'updated_at' => now(),
          ]);
        } catch (\Exception $e) {
        }

        // If konsultan is pendidikan, also save a copy into program_pendidikan
        if ($konsultanSpes && strpos($konsultanSpes, 'pendidikan') !== false) {
          try {
            \App\Models\ProgramPendidikan::create([
              'anak_didik_id' => $request->input('anak_didik_id'),
              'konsultan_id' => $request->input('konsultan_id'),
              'kode_program' => $it['kode_program'] ?? null,
              'nama_program' => $nama,
              'tujuan' => $it['tujuan'] ?? null,
              'aktivitas' => $it['aktivitas'] ?? null,
              'periode_mulai' => $request->input('periode_mulai'),
              'periode_selesai' => $request->input('periode_selesai'),
              'keterangan' => $request->input('keterangan'),
              'rekomendasi' => $request->input('rekomendasi'),
              'is_suggested' => $request->input('is_suggested') ? 1 : 0,
              'created_by' => auth()->id(),
            ]);
          } catch (\Exception $e) {
            // do not abort whole transaction on failure to create pendidikan record, but log
            \Log::error('Failed to create program_pendidikan: ' . $e->getMessage());
          }
        }
      }
    });

    return redirect()->route('program-anak.index')->with('success', 'Program Anak berhasil ditambahkan');
  }

  public function edit($id)
  {
    $program = ProgramAnak::findOrFail($id);
    $anakDidiks = AnakDidik::all();
    return view('content.program-anak.edit', compact('program', 'anakDidiks'));
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'anak_didik_id' => 'required|exists:anak_didiks,id',
      'program_konsultan_id' => 'nullable|exists:program_konsultan,id',
      'kode_program' => 'nullable|string|max:100',
      'nama_program' => 'required|string|max:255',
      'periode_mulai' => 'required|date',
      'periode_selesai' => 'required|date|after_or_equal:periode_mulai',
      'status' => 'required|in:aktif,selesai,nonaktif',
      'keterangan' => 'nullable|string',
      'rekomendasi' => 'nullable|string',
    ]);
    $program = ProgramAnak::findOrFail($id);
    $program->update($request->all());
    return redirect()->route('program-anak.index')->with('success', 'Program Anak berhasil diupdate');
  }

  public function destroy($id)
  {
    $program = ProgramAnak::findOrFail($id);
    $program->delete();
    return redirect()->route('program-anak.index')->with('success', 'Program Anak berhasil dihapus');
  }

  /**
   * AJAX: update a ProgramAnak record
   */
  public function updateJson(Request $request, $id)
  {
    // Load program first so we can authorize and adjust validation rules for psikologi entries
    $program = ProgramAnak::findOrFail($id);

    // Authorization: allow only admin, the creator, or the owning konsultan
    $user = auth()->user();
    if (!$user) {
      return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    }
    $allowed = false;
    if ($user->role === 'admin') {
      $allowed = true;
    }
    // allow original creator
    if (!$allowed && $program->created_by && intval($program->created_by) === intval($user->id)) {
      $allowed = true;
    }
    // allow konsultan owner when this program maps to a ProgramKonsultan
    if (!$allowed && $user->role === 'konsultan' && $program->program_konsultan_id) {
      $konsultanRecId = \App\Models\Konsultan::where('user_id', $user->id)->value('id');
      if (!$konsultanRecId && $user->email) {
        $konsultanRecId = \App\Models\Konsultan::where('email', $user->email)->value('id');
      }
      if (!$konsultanRecId) {
        $konsultanRecId = \App\Models\Konsultan::where('nama', $user->name)->value('id');
      }
      $progK = \App\Models\ProgramKonsultan::find($program->program_konsultan_id);
      if ($progK && $progK->konsultan_id && intval($progK->konsultan_id) === intval($konsultanRecId)) {
        $allowed = true;
      }
    }
    if (!$allowed) {
      return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
    }
    // conditional validation: if this program maps to a ProgramKonsultan, require nama_program; otherwise allow nama_program nullable (psikologi entries)
    $rules = [
      'kode_program' => 'nullable|string|max:100',
      'tujuan' => 'nullable|string',
      'aktivitas' => 'nullable|string',
      'rekomendasi' => 'nullable|string',
      'keterangan' => 'nullable|string',
    ];
    if ($program->program_konsultan_id) {
      $rules['nama_program'] = 'required|string|max:255';
    } else {
      $rules['nama_program'] = 'nullable|string|max:255';
    }
    $request->validate($rules);

    // compute changes for audit
    $changed = [];
    $fields = ['kode_program', 'nama_program', 'tujuan', 'aktivitas', 'rekomendasi', 'keterangan'];
    foreach ($fields as $f) {
      if ($request->has($f)) {
        $old = $program->{$f};
        $new = $request->input($f);
        if ($old != $new) {
          $changed[$f] = ['old' => $old, 'new' => $new];
        }
        $program->{$f} = $new;
      }
    }
    $program->save();

    // insert audit if any change
    if (!empty($changed)) {
      try {
        \DB::table('program_anak_audits')->insert([
          'program_anak_id' => $program->id,
          'action' => 'update',
          'user_id' => auth()->check() ? auth()->id() : null,
          'user_name' => auth()->check() ? (auth()->user()->name ?? null) : null,
          'changes' => json_encode($changed),
          'created_at' => now(),
          'updated_at' => now(),
        ]);
      } catch (\Exception $e) {
      }
    }

    return response()->json(['success' => true, 'message' => 'Program berhasil diupdate', 'program' => $program]);
  }

  /**
   * AJAX: delete a ProgramAnak record
   */
  public function destroyJson($id)
  {
    $program = ProgramAnak::findOrFail($id);
    // Authorization: allow only admin, the creator, or the owning konsultan
    $user = auth()->user();
    if (!$user) {
      return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    }
    $allowed = false;
    if ($user->role === 'admin') {
      $allowed = true;
    }
    if (!$allowed && $program->created_by && intval($program->created_by) === intval($user->id)) {
      $allowed = true;
    }
    if (!$allowed && $user->role === 'konsultan' && $program->program_konsultan_id) {
      $konsultanRecId = \App\Models\Konsultan::where('user_id', $user->id)->value('id');
      if (!$konsultanRecId && $user->email) {
        $konsultanRecId = \App\Models\Konsultan::where('email', $user->email)->value('id');
      }
      if (!$konsultanRecId) {
        $konsultanRecId = \App\Models\Konsultan::where('nama', $user->name)->value('id');
      }
      $progK = \App\Models\ProgramKonsultan::find($program->program_konsultan_id);
      if ($progK && $progK->konsultan_id && intval($progK->konsultan_id) === intval($konsultanRecId)) {
        $allowed = true;
      }
    }
    if (!$allowed) {
      return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
    }

    $program->delete();
    return response()->json(['success' => true, 'message' => 'Program berhasil dihapus']);
  }

  public function show($id)
  {
    $program = ProgramAnak::with('anakDidik')->findOrFail($id);
    return view('content.program-anak.show', compact('program'));
  }

  /**
   * Return program anak detail as JSON for modal display
   */
  public function showJson($id)
  {
    $program = ProgramAnak::with(['anakDidik', 'programKonsultan.konsultan'])->findOrFail($id);
    $data = [
      'id' => $program->id,
      'kode_program' => $program->kode_program,
      'nama_program' => $program->nama_program,
      'tujuan' => $program->tujuan,
      'aktivitas' => $program->aktivitas,
      'periode_mulai' => $program->periode_mulai ? $program->periode_mulai->toDateString() : null,
      'periode_selesai' => $program->periode_selesai ? $program->periode_selesai->toDateString() : null,
      'anak' => $program->anakDidik ? ['id' => $program->anakDidik->id, 'nama' => $program->anakDidik->nama] : null,
      'konsultan' => $program->programKonsultan && $program->programKonsultan->konsultan ? ['id' => $program->programKonsultan->konsultan->id, 'nama' => $program->programKonsultan->konsultan->nama] : null,
      'program_konsultan_id' => $program->program_konsultan_id,
      'created_at' => $program->created_at ? $program->created_at->toDateTimeString() : null,
    ];

    return response()->json(['success' => true, 'program' => $data]);
  }

  /**
   * Return latest ProgramPsikologi record for a given anak_didik as JSON
   */
  public function latestPsikologiForAnak($anakDidikId)
  {
    $rec = \App\Models\ProgramPsikologi::where('anak_didik_id', $anakDidikId)->orderByDesc('created_at')->first();
    if (!$rec) {
      return response()->json(['success' => true, 'data' => null]);
    }
    $data = [
      'id' => $rec->id,
      'latar_belakang' => $rec->latar_belakang ?? null,
      'metode_assessment' => $rec->metode_assessment ?? null,
      'hasil_assessment' => $rec->hasil_assessment ?? null,
      'diagnosa' => $rec->diagnosa_psikologi ?? ($rec->diagnosa ?? null),
      'kesimpulan' => $rec->kesimpulan ?? null,
      'rekomendasi' => $rec->rekomendasi ?? null,
      'created_at' => $rec->created_at ? $rec->created_at->toDateTimeString() : null,
    ];
    return response()->json(['success' => true, 'data' => $data]);
  }

  /**
   * Return ALL programs for an anak didik as JSON (flat list)
   */
  public function showAllForAnak($anakDidikId)
  {
    $items = ProgramAnak::with(['programKonsultan.konsultan'])
      ->where('anak_didik_id', $anakDidikId)
      ->orderByDesc('created_at')
      ->get();

    $out = [];
    foreach ($items as $it) {
      $out[] = [
        'id' => $it->id,
        'kode_program' => $it->kode_program,
        'nama_program' => $it->nama_program,
        'tujuan' => $it->tujuan,
        'aktivitas' => $it->aktivitas,
        'periode_mulai' => $it->periode_mulai ? $it->periode_mulai->toDateString() : null,
        'periode_selesai' => $it->periode_selesai ? $it->periode_selesai->toDateString() : null,
        'konsultan' => $it->programKonsultan && $it->programKonsultan->konsultan ? ['id' => $it->programKonsultan->konsultan->id, 'nama' => $it->programKonsultan->konsultan->nama] : null,
        'rekomendasi' => $it->rekomendasi ?? null,
        'keterangan' => $it->keterangan ?? null,
        'created_by' => $it->created_by ?? null,
        'created_by_name' => $it->created_by_name ?? null,
        'created_at' => $it->created_at ? $it->created_at->toDateTimeString() : null,
      ];
    }

    return response()->json(['success' => true, 'programs' => $out]);
  }
}
