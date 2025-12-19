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
    $programAnak = $query->get();
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
