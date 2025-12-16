<?php

namespace App\Http\Controllers;

use App\Models\ProgramAnak;
use App\Models\AnakDidik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgramAnakController extends Controller
{
  public function index()
  {
    $programAnak = ProgramAnak::with('anakDidik')->orderByDesc('created_at')->get();
    return view('content.program-anak.index', compact('programAnak'));
  }

  public function create()
  {
    $anakDidiks = AnakDidik::all();
    $konsultans = \App\Models\Konsultan::all();
    return view('content.program-anak.create', compact('anakDidiks', 'konsultans'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'anak_didik_id' => 'required|exists:anak_didiks,id',
      'nama_program' => 'required|string|max:255',
      'periode_mulai' => 'required|date',
      'periode_selesai' => 'required|date|after_or_equal:periode_mulai',
      'status' => 'required|in:aktif,selesai,nonaktif',
      'keterangan' => 'nullable|string',
    ]);
    ProgramAnak::create($request->all());
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
