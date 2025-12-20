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
    $query = ProgramAnak::with(['anakDidik', 'programKonsultan.konsultan'])->orderByDesc('created_at');
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
    if (auth()->check() && auth()->user()->role === 'konsultan') {
      $user = auth()->user();
      $k = \App\Models\Konsultan::where('user_id', $user->id)->value('spesialisasi');
      if (!$k && $user->email) {
        $k = \App\Models\Konsultan::where('email', $user->email)->value('spesialisasi');
      }
      if (!$k) {
        $k = \App\Models\Konsultan::where('nama', $user->name)->value('spesialisasi');
      }
      $currentKonsultanSpesRaw = $k;
    }
    return view('content.program-anak.index', compact('programAnak', 'currentKonsultanSpesRaw'));
  }

  public function create()
  {
    $anakDidiks = AnakDidik::all();
    $konsultans = \App\Models\Konsultan::all();
    // load program master grouped by konsultan for populating dropdowns in the form
    $programMasters = ProgramKonsultan::all()->groupBy('konsultan_id');
    return view('content.program-anak.create', compact('anakDidiks', 'konsultans', 'programMasters'));
  }

  public function storeProgramKonsultan(Request $request)
  {
    $request->validate([
      'kode_program' => 'nullable|string|max:100',
      'nama_program' => 'required|string|max:255',
      'tujuan' => 'nullable|string',
      'aktivitas' => 'nullable|string',
    ]);

    // normalize kode_program: remove non-alphanumeric and uppercase (no hyphens)
    $rawKode = $request->input('kode_program');
    $kodeSanitized = $rawKode ? strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $rawKode)) : null;

    ProgramKonsultan::create([
      'konsultan_id' => (function () {
        $user = auth()->user();
        if (!$user) return null;
        $konsultanId = \App\Models\Konsultan::where('user_id', $user->id)->value('id');
        if (!$konsultanId && $user->email) {
          $konsultanId = \App\Models\Konsultan::where('email', $user->email)->value('id');
        }
        if (!$konsultanId) {
          $konsultanId = \App\Models\Konsultan::where('nama', $user->name)->value('id');
        }
        return $konsultanId;
      })(),
      'kode_program' => $kodeSanitized,
      'nama_program' => $request->input('nama_program'),
      'tujuan' => $request->input('tujuan'),
      'aktivitas' => $request->input('aktivitas'),
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
    ]);

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
    $request->validate([
      'anak_didik_id' => 'required|exists:anak_didiks,id',
      'periode_mulai' => 'required|date',
      'periode_selesai' => 'required|date|after_or_equal:periode_mulai',
      'status' => 'nullable|in:aktif,selesai,nonaktif',
      'keterangan' => 'nullable|string',
      'program_items' => 'required|array|min:1',
    ]);

    $items = $request->input('program_items', []);

    \DB::transaction(function () use ($items, $request) {
      foreach ($items as $it) {
        // basic sanitation / mapping
        $nama = $it['nama_program'] ?? null;
        if (!$nama) continue; // skip empty rows

        ProgramAnak::create([
          'anak_didik_id' => $request->input('anak_didik_id'),
          'program_konsultan_id' => $it['program_konsultan_id'] ?? null,
          'kode_program' => $it['kode_program'] ?? null,
          'nama_program' => $nama,
          'tujuan' => $it['tujuan'] ?? null,
          'aktivitas' => $it['aktivitas'] ?? null,
          'periode_mulai' => $request->input('periode_mulai'),
          'periode_selesai' => $request->input('periode_selesai'),
          'status' => $request->input('status', 'aktif'),
          'keterangan' => $request->input('keterangan'),
          'is_suggested' => $request->input('is_suggested') ? 1 : 0,
        ]);
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

  public function show($id)
  {
    $program = ProgramAnak::with('anakDidik')->findOrFail($id);
    return view('content.program-anak.show', compact('program'));
  }
}
