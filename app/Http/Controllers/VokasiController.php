<?php

namespace App\Http\Controllers;

use App\Models\ProgramAnak;
use App\Models\AnakDidik;
use App\Models\ProgramKonsultan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\ActivityService;

class VokasiController extends Controller
{
  public function index()
  {
    // Build eligible anak list (ids) first (see below), then paginate AnakDidik
    // so we include anak who follow vokasi even if they have no ProgramAnak yet.
    // Determine eligible anak_didik ids: those with anak_didiks.vokasi_diikuti OR ProgramAnak rows with jenis_vokasi OR program masters with VOK kode
    $eligibleAnakIds = [];
    try {
      // AnakDidik rows with vokasi_diikuti JSON not empty
      $rows = \DB::table('anak_didiks')->select('id')
        ->whereNotNull('vokasi_diikuti')
        ->whereRaw("vokasi_diikuti != '[]'")
        ->get();
      foreach ($rows as $r) $eligibleAnakIds[] = $r->id;
    } catch (\Exception $e) {
      // ignore
    }

    try {
      // ProgramAnak rows that have jenis_vokasi (non-empty)
      $rows = ProgramAnak::whereNotNull('jenis_vokasi')->get(['anak_didik_id']);
      foreach ($rows as $r) if ($r->anak_didik_id) $eligibleAnakIds[] = $r->anak_didik_id;
    } catch (\Exception $e) {
    }

    try {
      // ProgramAnak rows whose ProgramKonsultan has a VOK kode
      $rows = ProgramAnak::whereHas('programKonsultan', function ($q) {
        try {
          $q->whereRaw("REPLACE(REPLACE(REPLACE(UPPER(kode_program),'-',''),' ',''),'.','') LIKE ?", ['VOK%']);
        } catch (\Exception $e) {
          $q->whereRaw("UPPER(kode_program) LIKE ?", ['VOK%']);
        }
      })->get(['anak_didik_id']);
      foreach ($rows as $r) if ($r->anak_didik_id) $eligibleAnakIds[] = $r->anak_didik_id;
    } catch (\Exception $e) {
    }

    $eligibleAnakIds = array_values(array_unique(array_filter($eligibleAnakIds)));

    // If none eligible, prepare empty paginator
    if (count($eligibleAnakIds) === 0) {
      $empty = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
      $vokasi = $empty;
    } else {
      // Build AnakDidik query (alphabetical)
      $anakQuery = AnakDidik::whereIn('id', $eligibleAnakIds)->orderBy('nama', 'asc');

      // search by anak name or program name (program name will be applied after we attach latest program)
      if (request('search')) {
        $search = request('search');
        $anakQuery->where('nama', 'like', "%$search%");
      }

      // filter by selected jenis vokasi (if provided) — check anak_didiks.vokasi_diikuti JSON and program_anak.jenis_vokasi
      if (request()->filled('filter_jenis_vokasi')) {
        $jenis = request('filter_jenis_vokasi');
        $anakQuery->where(function ($q) use ($jenis) {
          try {
            // MySQL JSON_CONTAINS
            $q->whereRaw("JSON_CONTAINS(vokasi_diikuti, ?)", ['"' . $jenis . '"']);
          } catch (\Exception $e) {
            // fallback to LIKE on serialized JSON
            $q->where('vokasi_diikuti', 'like', "%$jenis%");
          }

          // also include anak who have ProgramAnak rows with jenis_vokasi containing this jenis
          $q->orWhereExists(function ($sub) use ($jenis) {
            $sub->select(\DB::raw(1))
              ->from('program_anak')
              ->whereRaw('program_anak.anak_didik_id = anak_didiks.id')
              ->where(function ($sq) use ($jenis) {
                try {
                  $sq->whereRaw("JSON_CONTAINS(program_anak.jenis_vokasi, ?)", ['"' . $jenis . '"']);
                } catch (\Exception $e) {
                  $sq->where('program_anak.jenis_vokasi', 'like', "%$jenis%");
                }
              });
          });
        });
      }

      $anakPage = $anakQuery->paginate(10)->withQueryString();

      // For each AnakDidik in the page, attach their latest ProgramAnak (if any).
      $items = [];
      foreach ($anakPage->items() as $anak) {
        $pa = ProgramAnak::where('anak_didik_id', $anak->id)->orderByDesc('id')->first();
        if (!$pa) {
          $pa = new ProgramAnak();
          $pa->anak_didik_id = $anak->id;
          $pa->program_konsultan_id = null;
          $pa->kode_program = null;
          $pa->nama_program = null;
          $pa->periode_mulai = null;
          $pa->periode_selesai = null;
          $pa->status = null;
        }
        $pa->setRelation('anakDidik', $anak);
        $items[] = $pa;
      }

      // Create a paginator that mirrors the AnakDidik pagination but holds ProgramAnak-like items
      $vokasi = new \Illuminate\Pagination\LengthAwarePaginator(
        $items,
        $anakPage->total(),
        $anakPage->perPage(),
        $anakPage->currentPage(),
        ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
      );
    }
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
    // load anak didik list and program masters for admin modal dropdowns
    $anakDidiks = AnakDidik::orderBy('nama')->get();
    // only load program masters that belong to vokasi prefixes
    $vokasiPrefixes = ['PAI', 'COK', 'CRF', 'COM', 'GAR', 'BEA', 'AUT', 'HOU', 'VOK'];
    $pmQuery = ProgramKonsultan::orderBy('kode_program');
    $pmQuery->where(function ($q) use ($vokasiPrefixes) {
      foreach ($vokasiPrefixes as $idx => $pref) {
        if ($idx === 0) {
          $q->whereRaw("REPLACE(REPLACE(REPLACE(UPPER(kode_program),'-',''),' ',''),'.','') LIKE ?", [$pref . '%']);
        } else {
          $q->orWhereRaw("REPLACE(REPLACE(REPLACE(UPPER(kode_program),'-',''),' ',''),'.','') LIKE ?", [$pref . '%']);
        }
      }
    });
    $programMastersCollection = $pmQuery->get();
    $programMasters = $programMastersCollection->groupBy('konsultan_id');
    $programMastersFlat = $programMastersCollection->values()->all();

    // Build a map of latest non-empty jenis_vokasi per anak so the index can show a jenis.
    // Query across all ProgramAnak rows that have jenis_vokasi (ordered by id desc), then
    // pick the first occurrence per anak (which is the latest non-empty entry).
    $jenisVokasiMap = [];
    try {
      $rows = ProgramAnak::whereNotNull('jenis_vokasi')
        ->orderByDesc('id')
        ->get(['anak_didik_id', 'jenis_vokasi']);
      foreach ($rows as $r) {
        $aid = $r->anak_didik_id;
        if ($aid === null) continue;
        if (isset($jenisVokasiMap[$aid])) continue; // already have latest for this anak
        $decoded = is_array($r->jenis_vokasi) ? $r->jenis_vokasi : json_decode($r->jenis_vokasi, true);
        if (is_array($decoded) && count($decoded) > 0) {
          $jenisVokasiMap[$aid] = $decoded;
        }
      }
    } catch (\Exception $e) {
      $jenisVokasiMap = [];
    }

    return view('content.vokasi.index', compact('vokasi', 'currentKonsultanSpesRaw', 'currentKonsultanId', 'anakDidiks', 'programMasters', 'jenisVokasiMap'));
  }

  /**
   * Store a simple Vokasi entry for a selected anak didik (admin)
   */
  public function storeAnakDidik(Request $request)
  {
    $this->validate($request, [
      'anak_didik_id' => 'required|exists:anak_didiks,id',
      'program_konsultan_id' => 'nullable|exists:program_konsultan,id',
      'jenis_vokasi_text' => 'nullable|string|max:255',
    ]);

    $anakId = $request->input('anak_didik_id');
    $progKId = $request->input('program_konsultan_id');
    $jenisText = $request->input('jenis_vokasi_text');

    if (!$progKId && empty($jenisText)) {
      return redirect()->back()->with('error', 'Pilih jenis vokasi atau isi jenis vokasi.');
    }

    $namaProgram = null;
    if ($progKId) {
      $master = ProgramKonsultan::find($progKId);
      $namaProgram = $master ? ($master->nama_program ?? null) : null;
    }
    if (!$namaProgram) $namaProgram = $jenisText;

    $created = ProgramAnak::create([
      'anak_didik_id' => $anakId,
      'program_konsultan_id' => $progKId ?: null,
      'kode_program' => $progKId ? ($master->kode_program ?? null) : null,
      'nama_program' => $namaProgram,
      'tujuan' => null,
      'aktivitas' => null,
      'periode_mulai' => now()->toDateString(),
      'periode_selesai' => now()->addMonth()->toDateString(),
      'status' => 'aktif',
      'keterangan' => null,
      'rekomendasi' => null,
      'created_by' => auth()->check() ? auth()->id() : null,
      'created_by_name' => auth()->check() ? (auth()->user()->name ?? null) : null,
      'is_suggested' => 0,
    ]);

    return redirect()->back()->with('success', 'Anak didik berhasil ditambahkan ke Vokasi.');
  }

  /**
   * Return JSON list of programs assigned to an anak didik, grouped by konsultan/user.
   */
  public function riwayatProgram($anakDidikId)
  {
    // Return program entries that belong to known vokasi prefixes (normalize kode_program when matching)
    $vokasiPrefixes = ['PAI', 'COK', 'CRF', 'COM', 'GAR', 'BEA', 'AUT', 'HOU', 'VOK'];
    $items = ProgramAnak::with(['programKonsultan.konsultan', 'programKonsultan'])
      ->where('anak_didik_id', $anakDidikId)
      ->where(function ($q) use ($vokasiPrefixes) {
        // match kode_program prefixes
        $q->where(function ($qq) use ($vokasiPrefixes) {
          foreach ($vokasiPrefixes as $idx => $pref) {
            if ($idx === 0) {
              try {
                $qq->whereRaw("REPLACE(REPLACE(REPLACE(UPPER(kode_program),'-',''),' ',''),'.','') LIKE ?", [$pref . '%']);
              } catch (\Exception $e) {
                $qq->whereRaw("UPPER(kode_program) LIKE ?", [$pref . '%']);
              }
            } else {
              try {
                $qq->orWhereRaw("REPLACE(REPLACE(REPLACE(UPPER(kode_program),'-',''),' ',''),'.','') LIKE ?", [$pref . '%']);
              } catch (\Exception $e) {
                $qq->orWhereRaw("UPPER(kode_program) LIKE ?", [$pref . '%']);
              }
            }
          }
        });

        // also include rows whose program_konsultan (master) has a vokasi kode
        $q->orWhereHas('programKonsultan', function ($q2) use ($vokasiPrefixes) {
          foreach ($vokasiPrefixes as $idx => $pref) {
            if ($idx === 0) {
              try {
                $q2->whereRaw("REPLACE(REPLACE(REPLACE(UPPER(kode_program),'-',''),' ',''),'.','') LIKE ?", [$pref . '%']);
              } catch (\Exception $e) {
                $q2->whereRaw("UPPER(kode_program) LIKE ?", [$pref . '%']);
              }
            } else {
              try {
                $q2->orWhereRaw("REPLACE(REPLACE(REPLACE(UPPER(kode_program),'-',''),' ',''),'.','') LIKE ?", [$pref . '%']);
              } catch (\Exception $e) {
                $q2->orWhereRaw("UPPER(kode_program) LIKE ?", [$pref . '%']);
              }
            }
          }
        });
      })
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
        'program_konsultan_id' => $it->program_konsultan_id ?? null,
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
        'program_konsultan_id' => $it->program_konsultan_id ?? null,
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
    // include all known vokasi prefixes so kode dropdowns can show per-jenis vokasi masters
    $vokasiPrefixes = ['PAI', 'COK', 'CRF', 'COM', 'GAR', 'BEA', 'AUT', 'HOU', 'VOK'];
    $pmQuery = ProgramKonsultan::orderBy('kode_program');
    $pmQuery->where(function ($q) use ($vokasiPrefixes) {
      foreach ($vokasiPrefixes as $idx => $pref) {
        if ($idx === 0) {
          $q->whereRaw("REPLACE(REPLACE(REPLACE(UPPER(kode_program),'-',''),' ',''),'.','') LIKE ?", [$pref . '%']);
        } else {
          $q->orWhereRaw("REPLACE(REPLACE(REPLACE(UPPER(kode_program),'-',''),' ',''),'.','') LIKE ?", [$pref . '%']);
        }
      }
    });
    $programMastersCollection = $pmQuery->get();
    $programMasters = $programMastersCollection->groupBy('konsultan_id');
    $programMastersFlat = $programMastersCollection->values()->all();
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
    return view('content.vokasi.create', compact('anakDidiks', 'konsultans', 'programMasters', 'programMastersFlat', 'currentKonsultanId', 'currentKonsultanSpesRaw'));
  }

  /**
   * Return next kode_program for a given jenis vokasi (prefix mapping + numeric suffix increment)
   */
  public function nextKodeForJenis($jenis)
  {
    $mapping = [
      'Painting' => 'PAI',
      'Cooking' => 'COK',
      'Craft' => 'CRF',
      'Computer' => 'COM',
      'Gardening' => 'GAR',
      'Beauty' => 'BEA',
      'Auto Wash' => 'AUT',
      'House Keeping' => 'HOU',
    ];
    $prefix = isset($mapping[$jenis]) ? $mapping[$jenis] : strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $jenis), 0, 3));

    try {
      $pk = \App\Models\ProgramKonsultan::select('kode_program')->get()->pluck('kode_program')->toArray();
      $pa = \App\Models\ProgramAnak::select('kode_program')->whereNotNull('kode_program')->get()->pluck('kode_program')->toArray();
      $rows = array_values(array_unique(array_filter(array_merge($pk, $pa))));
      $maxNum = 0;
      $maxDigits = 3;
      foreach ($rows as $k) {
        if (!$k) continue;
        $norm = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($k));
        if (strpos($norm, $prefix) !== 0) continue;
        $suffix = substr($norm, strlen($prefix));
        if (preg_match('/^(0*)(\d+)$/', $suffix, $m)) {
          $num = intval($m[2]);
          $digits = strlen($m[2]);
          $maxDigits = max($maxDigits, $digits);
          if ($num > $maxNum) $maxNum = $num;
        }
      }
      $next = $maxNum + 1;
      $pad = $maxDigits;
      $nextStr = str_pad((string)$next, $pad, '0', STR_PAD_LEFT);
      $nextKode = $prefix . $nextStr;
      return response()->json(['success' => true, 'next' => $nextKode, 'prefix' => $prefix, 'nextNumber' => $next, 'pad' => $pad]);
    } catch (\Exception $e) {
      return response()->json(['success' => false, 'message' => 'Gagal menghitung kode'], 500);
    }
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

    // ensure only konsultan with spesialisasi pendidikan can create vokasi program
    $konsultanRec = null;
    if ($konsultanId) {
      $konsultanRec = \App\Models\Konsultan::find($konsultanId);
    }
    $spes = $konsultanRec ? strtolower($konsultanRec->spesialisasi ?? '') : '';
    if (!($spes && strpos($spes, 'pendidikan') !== false)) {
      // if current user is not konsultan pendidikan, forbid creation
      return redirect()->back()->with('error', 'Hanya konsultan spesialisasi Pendidikan yang dapat menambah program Vokasi.');
    }

    // If client submitted a kode_program, use it (sanitized). Otherwise, generate next kode based on selected jenis_vokasi.
    $submittedKode = $request->input('kode_program');
    $finalKode = null;
    if ($submittedKode && trim($submittedKode) !== '') {
      $finalKode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $submittedKode));
    } else {
      $jenis = $request->input('jenis_vokasi');
      $mapping = [
        'Painting' => 'PAI',
        'Cooking' => 'COK',
        'Craft' => 'CRF',
        'Computer' => 'COM',
        'Gardening' => 'GAR',
        'Beauty' => 'BEA',
        'Auto Wash' => 'AUT',
        'House Keeping' => 'HOU',
      ];
      $prefix = isset($mapping[$jenis]) ? $mapping[$jenis] : strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $jenis ?? ''), 0, 3));

      // gather existing kode_program values from masters and anak programs
      $masters = ProgramKonsultan::whereNotNull('kode_program')->get()->pluck('kode_program')->toArray();
      $anakRows = ProgramAnak::whereNotNull('kode_program')->get()->pluck('kode_program')->toArray();
      $all = array_values(array_unique(array_filter(array_merge($masters, $anakRows))));

      $maxNum = 0;
      $maxDigits = 3;
      foreach ($all as $k) {
        if (!$k) continue;
        $norm = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($k));
        if (strpos($norm, $prefix) !== 0) continue;
        $suffix = substr($norm, strlen($prefix));
        if (preg_match('/^(0*)(\d+)$/', $suffix, $m)) {
          $num = intval($m[2]);
          $digits = strlen($m[2]);
          $maxDigits = max($maxDigits, $digits);
          if ($num > $maxNum) $maxNum = $num;
        }
      }
      $nextNum = $maxNum + 1;
      $finalKode = $prefix . str_pad((string)$nextNum, $maxDigits, '0', STR_PAD_LEFT);
    }

    ProgramKonsultan::create([
      'konsultan_id' => $konsultanId,
      'kode_program' => $finalKode,
      'nama_program' => $request->input('nama_program'),
      'tujuan' => $request->input('tujuan'),
      'aktivitas' => $request->input('aktivitas'),
      'keterangan' => $request->input('keterangan'),
    ]);

    // Log aktivitas jika action dilakukan oleh akun konsultan
    $user = auth()->user();
    if ($user && $user->role === 'konsultan') {
      // try to find the newly created program to get its id and name
      try {
        $p = \App\Models\ProgramKonsultan::where('konsultan_id', $konsultanId)
          ->where('nama_program', $request->input('nama_program'))
          ->orderByDesc('id')
          ->first();
        if ($p) ActivityService::logCreate('ProgramKonsultan', $p->id, 'Membuat daftar program: ' . ($p->nama_program ?? ''));
      } catch (\Exception $e) {
      }
    }

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

    // Filter by selected jenis vokasi (map to prefix), or show all known vokasi prefixes when none selected
    $jenisMap = [
      'Painting' => 'PAI',
      'Cooking' => 'COK',
      'Craft' => 'CRF',
      'Computer' => 'COM',
      'Gardening' => 'GAR',
      'Beauty' => 'BEA',
      'Auto Wash' => 'AUT',
      'House Keeping' => 'HOU',
    ];
    $selectedJenis = request('filter_jenis_vokasi');
    if ($selectedJenis) {
      $selPrefix = isset($jenisMap[$selectedJenis]) ? $jenisMap[$selectedJenis] : strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $selectedJenis), 0, 3));
      try {
        $query->whereRaw("REPLACE(REPLACE(REPLACE(UPPER(kode_program),'-',''),' ',''),'.','') LIKE ?", [$selPrefix . '%']);
      } catch (\Exception $e) {
        $query->whereRaw("UPPER(kode_program) LIKE ?", [$selPrefix . '%']);
      }
    } else {
      $vokasiPrefixes = array_values($jenisMap);
      $vokasiPrefixes[] = 'VOK';
      $query->where(function ($q) use ($vokasiPrefixes) {
        foreach ($vokasiPrefixes as $idx => $pref) {
          if ($idx === 0) {
            $q->whereRaw("REPLACE(REPLACE(REPLACE(UPPER(kode_program),'-',''),' ',''),'.','') LIKE ?", [$pref . '%']);
          } else {
            $q->orWhereRaw("REPLACE(REPLACE(REPLACE(UPPER(kode_program),'-',''),' ',''),'.','') LIKE ?", [$pref . '%']);
          }
        }
      });
    }

    // order by kode_program prefix then numeric suffix (natural ordering like WIC001, WIC002)
    // Use MySQL 8+ regex functions if available: order by prefix (non-numeric part) then numeric suffix cast to integer
    try {
      // strip non-alphanumeric characters when ordering (so SI-001 and SI001 are treated same)
      $programs = $query->orderByRaw("REGEXP_REPLACE(REGEXP_REPLACE(kode_program, '[^A-Za-z0-9]', ''), '\\d+$', '') ASC")
        ->orderByRaw("CAST(REGEXP_SUBSTR(REGEXP_REPLACE(kode_program, '[^0-9]', ''), '\\d+$') AS UNSIGNED) ASC")
        ->paginate(10)->withQueryString();
    } catch (\Exception $e) {
      // fallback to ordering by sanitized kode_program string if regex functions not available
      $programs = $query->orderByRaw("REPLACE(REPLACE(REPLACE(kode_program,'-',''),' ',''),'.','') ASC")->paginate(10)->withQueryString();
    }

    // For Vokasi we always use VOK prefix
    $prefix = 'VOK';
    try {
      $lastKode = ProgramKonsultan::whereRaw("REPLACE(REPLACE(REPLACE(UPPER(kode_program),'-',''),' ',''),'.','') LIKE ?", [$prefix . '%'])->orderByDesc('id')->value('kode_program');
    } catch (\Exception $e) {
      $lastKode = ProgramKonsultan::whereRaw("UPPER(kode_program) LIKE ?", [$prefix . '%'])->orderByDesc('id')->value('kode_program');
    }

    $nextKode = 'VOK001';
    if ($lastKode) {
      if (preg_match('/^(.*?)(\d+)$/', $lastKode, $m)) {
        $num = intval($m[2]) + 1;
        $digits = strlen($m[2]);
        $nextKode = $prefix . str_pad($num, $digits, '0', STR_PAD_LEFT);
      } else {
        $nextKode = $prefix . '001';
      }
    }

    return view('content.vokasi.daftar-program', compact('programs', 'nextKode'));
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

    // Log aktivitas jika action dilakukan oleh akun konsultan
    $user = auth()->user();
    if ($user && $user->role === 'konsultan') {
      ActivityService::logUpdate('ProgramKonsultan', $program->id, 'Mengupdate daftar program: ' . ($program->nama_program ?? ''));
    }

    if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
      return response()->json(['success' => true, 'message' => 'Daftar program berhasil diupdate', 'program' => $program]);
    }

    return redirect()->route('vokasi.daftar-program')->with('success', 'Daftar program berhasil diupdate');
  }

  public function destroyProgramKonsultan($id)
  {
    $program = ProgramKonsultan::findOrFail($id);
    $programName = $program->nama_program ?? null;
    $program->delete();

    // Log aktivitas jika action dilakukan oleh akun konsultan
    $user = auth()->user();
    if ($user && $user->role === 'konsultan') {
      ActivityService::logDelete('ProgramKonsultan', $id, 'Menghapus daftar program: ' . ($programName ?? ''));
    }

    return redirect()->route('vokasi.daftar-program')->with('success', 'Daftar program berhasil dihapus');
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
      'vokasi_diikuti' => 'nullable|array',
      'vokasi_diikuti.*' => 'string|max:100',
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

      // Log aktivitas jika dibuat oleh akun konsultan
      try {
        $user = auth()->user();
        if ($user && $user->role === 'konsultan') {
          $anak = AnakDidik::find($created->anak_didik_id);
          $name = $anak ? trim($anak->nama) : ('ID ' . $created->anak_didik_id);
          $desc = 'Membuat program (' . $name . ')';
          ActivityService::logCreate('ProgramAnak', $created->id, $desc);
        }
      } catch (\Exception $e) {
      }

      // Persist vokasi_diikuti on anak (if provided)
      try {
        $voks = $request->input('vokasi_diikuti', null);
        if (is_array($voks)) {
          $anak = AnakDidik::find($request->input('anak_didik_id'));
          if ($anak) {
            $anak->vokasi_diikuti = $voks ?: null;
            $anak->save();
          }
        }
      } catch (\Exception $e) {
      }

      return redirect()->route('vokasi.index')->with('success', 'Rekomendasi psikologi berhasil disimpan');
    }

    // Default flow for non-psikologi konsultan: program_items required
    $request->validate([
      'program_items' => 'required|array|min:1',
    ]);

    $items = $request->input('program_items', []);
    $selectedJenis = [];

    // collect created kode_program values so we can log a single activity after transaction
    $createdCodes = [];

    \DB::transaction(function () use ($items, $request, $konsultanSpes, $selectedJenis, &$createdCodes) {
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
          // jenis_vokasi intentionally not set here; AnakDidik.vokasi_diikuti is the source of truth
          'jenis_vokasi' => null,
          'periode_mulai' => $request->input('periode_mulai'),
          'periode_selesai' => $request->input('periode_selesai'),
          'status' => $request->input('status', 'aktif'),
          'keterangan' => $request->input('keterangan'),
          'rekomendasi' => $request->input('rekomendasi'),
          'created_by' => $creatorId,
          'created_by_name' => $creatorName,
          'is_suggested' => $request->input('is_suggested') ? 1 : 0,
        ]);

        // audit: record create for each program itema
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

        // collect created kode_program for logging
        if (!empty($programAnak->kode_program)) $createdCodes[] = trim($programAnak->kode_program);

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

    // After creating program items, persist vokasi_diikuti on AnakDidik (if provided)
    try {
      $voks = $request->input('vokasi_diikuti', null);
      if (is_array($voks)) {
        $anak = AnakDidik::find($request->input('anak_didik_id'));
        if ($anak) {
          $anak->vokasi_diikuti = $voks ?: null;
          $anak->save();
        }
      }
    } catch (\Exception $e) {
    }

    // After successful transaction, create one activity log entry (if performed by konsultan)
    try {
      $user = auth()->user();
      if ($user && $user->role === 'konsultan') {
        $anakId = $request->input('anak_didik_id');
        $anak = AnakDidik::find($anakId);
        $name = $anak ? trim($anak->nama) : ('ID ' . $anakId);
        $codes = array_values(array_unique(array_filter($createdCodes)));
        $desc = 'Membuat program (' . $name . ')';
        if (!empty($codes)) {
          $desc .= ' dengan program (' . implode(', ', $codes) . ')';
        }
        ActivityService::logCreate('ProgramAnak', null, $desc);
      }
    } catch (\Exception $e) {
    }

    return redirect()->route('vokasi.index')->with('success', 'Program Anak berhasil ditambahkan');
  }

  public function edit($id)
  {
    $program = ProgramAnak::findOrFail($id);
    $anakDidiks = AnakDidik::all();
    return view('content.vokasi.edit', compact('program', 'anakDidiks'));
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
    $program->update($request->except(['_token', '_method']));

    // Log aktivitas jika diupdate oleh akun konsultan
    try {
      $user = auth()->user();
      if ($user && $user->role === 'konsultan') {
        $anak = AnakDidik::find($program->anak_didik_id);
        $name = $anak ? trim($anak->nama) : ('ID ' . $program->anak_didik_id);
        $code = $program->kode_program ?? null;
        $desc = 'Mengupdate program (' . $name . ')';
        if (!empty($code)) {
          $desc .= ' dengan program (' . $code . ')';
        }
        ActivityService::logUpdate('ProgramAnak', $program->id, $desc);
      }
    } catch (\Exception $e) {
    }

    return redirect()->route('vokasi.index')->with('success', 'Program Anak berhasil diupdate');
  }

  public function destroy($id)
  {
    $program = ProgramAnak::findOrFail($id);

    // prepare description for log before deleting
    try {
      $user = auth()->user();
      if ($user && $user->role === 'konsultan') {
        $anak = AnakDidik::find($program->anak_didik_id);
        $name = $anak ? trim($anak->nama) : ('ID ' . $program->anak_didik_id);
        $code = $program->kode_program ?? null;
        $desc = 'Menghapus program (' . $name . ')';
        if (!empty($code)) {
          $desc .= ' dengan program (' . $code . ')';
        }
        ActivityService::logDelete('ProgramAnak', $program->id, $desc);
      }
    } catch (\Exception $e) {
    }

    $program->delete();
    return redirect()->route('vokasi.index')->with('success', 'Program Anak berhasil dihapus');
  }

  /**
   * AJAX: update a ProgramAnak record
   */
  public function updateJson(Request $request, $id)
  {
    $program = ProgramAnak::findOrFail($id);

    // Authorization: allow only admin, the creator, or the owning konsultan
    $user = auth()->user();
    if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

    $allowed = false;
    if ($user->role === 'admin') $allowed = true;
    if (!$allowed && $program->created_by && intval($program->created_by) === intval($user->id)) $allowed = true;
    if (!$allowed && $user->role === 'konsultan' && $program->program_konsultan_id) {
      $konsultanRecId = \App\Models\Konsultan::where('user_id', $user->id)->value('id');
      if (!$konsultanRecId && $user->email) $konsultanRecId = \App\Models\Konsultan::where('email', $user->email)->value('id');
      if (!$konsultanRecId) $konsultanRecId = \App\Models\Konsultan::where('nama', $user->name)->value('id');
      $progK = \App\Models\ProgramKonsultan::find($program->program_konsultan_id);
      if ($progK && $progK->konsultan_id && intval($progK->konsultan_id) === intval($konsultanRecId)) $allowed = true;
    }
    if (!$allowed) return response()->json(['success' => false, 'message' => 'Forbidden'], 403);

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
        if ($old != $new) $changed[$f] = ['old' => $old, 'new' => $new];
        $program->{$f} = $new;
      }
    }
    $program->save();

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

      try {
        if ($user && $user->role === 'konsultan') {
          $anak = AnakDidik::find($program->anak_didik_id);
          $name = $anak ? trim($anak->nama) : ('ID ' . $program->anak_didik_id);
          $code = $program->kode_program ?? null;
          $desc = 'Mengupdate program (' . $name . ')';
          if (!empty($code)) $desc .= ' dengan program (' . $code . ')';
          ActivityService::logUpdate('ProgramAnak', $program->id, $desc);
        }
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
    $user = auth()->user();
    if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

    $allowed = false;
    if ($user->role === 'admin') $allowed = true;
    if (!$allowed && $program->created_by && intval($program->created_by) === intval($user->id)) $allowed = true;
    if (!$allowed && $user->role === 'konsultan' && $program->program_konsultan_id) {
      $konsultanRecId = \App\Models\Konsultan::where('user_id', $user->id)->value('id');
      if (!$konsultanRecId && $user->email) $konsultanRecId = \App\Models\Konsultan::where('email', $user->email)->value('id');
      if (!$konsultanRecId) $konsultanRecId = \App\Models\Konsultan::where('nama', $user->name)->value('id');
      $progK = \App\Models\ProgramKonsultan::find($program->program_konsultan_id);
      if ($progK && $progK->konsultan_id && intval($progK->konsultan_id) === intval($konsultanRecId)) $allowed = true;
    }
    if (!$allowed) return response()->json(['success' => false, 'message' => 'Forbidden'], 403);

    try {
      if ($user && $user->role === 'konsultan') {
        $anak = AnakDidik::find($program->anak_didik_id);
        $name = $anak ? trim($anak->nama) : ('ID ' . $program->anak_didik_id);
        $code = $program->kode_program ?? null;
        $desc = 'Menghapus program (' . $name . ')';
        if (!empty($code)) $desc .= ' dengan program (' . $code . ')';
        ActivityService::logDelete('ProgramAnak', $program->id, $desc);
      }
    } catch (\Exception $e) {
    }

    $program->delete();
    return response()->json(['success' => true, 'message' => 'Program berhasil dihapus']);
  }

  public function show($id)
  {
    $program = ProgramAnak::with('anakDidik')->findOrFail($id);
    return view('content.vokasi.show', compact('program'));
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

    // include keterangan and fallback to master keterangan when available
    $data['keterangan'] = $program->keterangan ?? null;
    $masterKeterangan = null;
    try {
      if ($program->programKonsultan && isset($program->programKonsultan->keterangan)) {
        $masterKeterangan = $program->programKonsultan->keterangan;
      }
      // fallback: lookup by kode_program if master relation absent
      if (!$masterKeterangan && $program->kode_program) {
        $kodeSanitized = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $program->kode_program));
        if ($kodeSanitized) {
          $masterRec = ProgramKonsultan::whereRaw("REPLACE(REPLACE(REPLACE(kode_program,'-',''),' ',''),'.','') = ?", [$kodeSanitized])->first();
          if ($masterRec) $masterKeterangan = $masterRec->keterangan ?? null;
        }
      }
    } catch (\Exception $e) {
      $masterKeterangan = null;
    }
    $data['keterangan_master'] = $masterKeterangan;

    // include anak vokasi selections and program jenis_vokasi if present
    try {
      $data['anak_vokasi'] = $program->anakDidik ? ($program->anakDidik->vokasi_diikuti ?? null) : null;
    } catch (\Exception $e) {
      $data['anak_vokasi'] = null;
    }
    try {
      $data['jenis_vokasi'] = $program->jenis_vokasi ?? null;
    } catch (\Exception $e) {
      $data['jenis_vokasi'] = null;
    }

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
