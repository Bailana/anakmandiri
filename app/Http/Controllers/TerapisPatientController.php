<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GuruAnakDidik;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

use App\Models\AnakDidik;


class TerapisPatientController extends Controller
{
  /**
   * Display list of patients for a terapis (or allow admin to view/select terapis).
   */
  public function index(Request $request)
  {
    $user = Auth::user();
    $selectedTherapisId = $request->query('user_id');
    $search = $request->query('search');
    $therapists = collect();

    // Build base query depending on role
    if ($user->role === 'terapis') {
      $query = GuruAnakDidik::with(['anakDidik', 'user'])
        ->where('user_id', $user->id);

      if ($request->filled('search')) {
        $query->whereHas('anakDidik', function ($q) use ($search) {
          $q->where('nama', 'like', "%{$search}%");
        });
      }

      $assignments = $query->orderBy('id', 'desc')->paginate(10)->appends($request->query());

      return view('content.terapis.patients', compact('assignments', 'therapists', 'user', 'selectedTherapisId'));
    }

    // admin view: list therapists and optionally filter by therapist id
    $therapists = User::where('role', 'terapis')->get();

    $query = GuruAnakDidik::with(['anakDidik', 'user']);
    if ($selectedTherapisId) {
      $query->where('user_id', $selectedTherapisId);
    } else {
      $query->whereIn('user_id', $therapists->pluck('id')->toArray());
    }

    if ($request->filled('search')) {
      $query->whereHas('anakDidik', function ($q) use ($search) {
        $q->where('nama', 'like', "%{$search}%");
      });
    }

    $assignments = $query->orderBy('id', 'desc')->paginate(10)->appends($request->query());

    return view('content.terapis.patients', compact('assignments', 'therapists', 'user', 'selectedTherapisId'));
  }

  /**
   * Show form to assign a patient to a terapis
   */
  public function create(Request $request)
  {
    $user = Auth::user();
    $therapists = collect();
    if ($user->role === 'terapis') {
      $selectedTherapisId = $user->id;
    } else {
      $therapists = User::where('role', 'terapis')->get();
      $selectedTherapisId = $request->query('user_id');
    }

    $anakDidiks = AnakDidik::orderBy('nama')->get();
    return view('content.terapis.create_patient', compact('therapists', 'user', 'selectedTherapisId', 'anakDidiks'));
  }

  /**
   * Store a new GuruAnakDidik assignment
   */
  public function store(Request $request)
  {
    $user = Auth::user();
    $data = $request->validate([
      'user_id' => 'nullable|exists:users,id',
      'anak_didik_id' => 'required|exists:anak_didik,id',
      'tanggal_mulai' => 'nullable|date',
      'status' => 'nullable|string|max:191',
    ]);

    $terapisId = $user->role === 'terapis' ? $user->id : ($data['user_id'] ?? null);
    if (!$terapisId) {
      return back()->withInput()->withErrors(['user_id' => 'Pilih terapis.']);
    }

    GuruAnakDidik::create([
      'user_id' => $terapisId,
      'anak_didik_id' => $data['anak_didik_id'],
      'status' => $data['status'] ?? 'aktif',
      'tanggal_mulai' => $data['tanggal_mulai'] ?? now(),
    ]);

    return redirect()->route('terapis.pasien.index')->with('success', 'Pasien berhasil ditambahkan untuk terapis.');
  }
}
