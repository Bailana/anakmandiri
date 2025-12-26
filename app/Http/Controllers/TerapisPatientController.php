<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GuruAnakDidik;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

use App\Models\AnakDidik;
use App\Models\GuruAnakDidikSchedule;
use Illuminate\Support\Facades\Log;


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
    // always provide list of therapists to view (for dropdowns)
    $therapists = User::where('role', 'terapis')->get();
    $selectedTherapisId = $user->role === 'terapis' ? $user->id : $request->query('user_id');

    $anakDidiks = AnakDidik::orderBy('nama')->get();
    return view('content.terapis.create_patient', compact('therapists', 'user', 'selectedTherapisId', 'anakDidiks'));
  }

  /**
   * Show edit form for an existing assignment
   */
  public function edit($id)
  {
    $user = Auth::user();
    $therapists = User::where('role', 'terapis')->get();
    $assignment = GuruAnakDidik::with('schedules')->findOrFail($id);
    $selectedTherapisId = $assignment->user_id;
    $anakDidiks = AnakDidik::orderBy('nama')->get();
    return view('content.terapis.edit_patient', compact('therapists', 'user', 'selectedTherapisId', 'anakDidiks', 'assignment'));
  }

  /**
   * Store a new GuruAnakDidik assignment
   */
  public function store(Request $request)
  {
    $user = Auth::user();
    // normalize time formats submitted by the client (allow 13.00 -> 13:00)
    $input = $request->all();
    // normalize time formats: allow 13.00, 13:00:00, 9:05 -> convert to 13:00 / 09:05
    $normalizeTime = function ($t) {
      if ($t === null || $t === '') return null;
      $t = str_replace('.', ':', $t);
      // trim seconds if present (HH:MM:SS -> HH:MM)
      $t = preg_replace('/^(\d{1,2}:\d{2}).*/', '$1', $t);
      // pad single-digit hour
      if (preg_match('/^\d:\d{2}$/', $t)) $t = '0' . $t;
      return $t;
    };

    if (!empty($input['jam_mulai'])) {
      $input['jam_mulai'] = $normalizeTime($input['jam_mulai']);
    }
    if (!empty($input['schedules']) && is_array($input['schedules'])) {
      foreach ($input['schedules'] as $k => $s) {
        if (!empty($s['jam_mulai'])) {
          $input['schedules'][$k]['jam_mulai'] = $normalizeTime($s['jam_mulai']);
        }
      }
    }
    $request->merge($input);
    Log::info('TerapisPatientController@store merged input', ['input' => $request->all()]);

    $data = $request->validate([
      'user_id' => 'nullable|exists:users,id',
      // table name is plural in DB (anak_didiks)
      'anak_didik_id' => 'required|exists:anak_didiks,id',
      'tanggal_mulai' => 'nullable|date',
      'status' => 'nullable|in:aktif,non-aktif',
      'jam_mulai' => 'nullable|date_format:H:i',
      'jenis_terapi' => 'nullable|string|max:191',
      'terapis_nama' => 'nullable|string|max:191',
      'schedules' => 'nullable|array',
      // schedules now use tanggal_mulai instead of hari
      'schedules.*.tanggal_mulai' => 'nullable|date',
      'schedules.*.jam_mulai' => 'nullable|date_format:H:i',
    ]);

    $terapisId = $user->role === 'terapis' ? $user->id : ($data['user_id'] ?? null);
    if (!$terapisId) {
      return back()->withInput()->withErrors(['user_id' => 'Pilih terapis.']);
    }

    // Prevent duplicate assignment for same terapis and anak_didik
    $exists = GuruAnakDidik::where('user_id', $terapisId)
      ->where('anak_didik_id', $data['anak_didik_id'])
      ->first();
    if ($exists) {
      return back()->withInput()->withErrors(['anak_didik_id' => 'Pasien sudah terdaftar untuk terapis ini.']);
    }

    $assignment = GuruAnakDidik::create([
      'user_id' => $terapisId,
      'anak_didik_id' => $data['anak_didik_id'],
      'status' => $data['status'] ?? 'aktif',
      'tanggal_mulai' => $data['tanggal_mulai'] ?? now(),
      'jam_mulai' => $data['jam_mulai'] ?? null,
      'jenis_terapi' => $data['jenis_terapi'] ?? null,
      'terapis_nama' => $data['terapis_nama'] ?? optional(User::find($terapisId))->name ?? null,
    ]);

    // store multiple schedules if provided
    if (!empty($data['schedules']) && is_array($data['schedules'])) {
      foreach ($data['schedules'] as $s) {
        // skip empty rows
        if (empty($s['tanggal_mulai']) && empty($s['jam_mulai'])) continue;
        GuruAnakDidikSchedule::create([
          'guru_anak_didik_id' => $assignment->id,
          // legacy `hari` column still exists in DB and may be NOT NULL;
          // provide an empty string fallback to avoid SQL errors until the
          // schema is migrated to allow nulls or the column removed.
          'hari' => $s['hari'] ?? '',
          'tanggal_mulai' => $s['tanggal_mulai'] ?? null,
          'jam_mulai' => $s['jam_mulai'] ?? null,
        ]);
      }
    }

    return redirect()->route('terapis.pasien.index')->with('success', 'Pasien berhasil ditambahkan untuk terapis.');
  }

  /**
   * Update an existing GuruAnakDidik assignment
   */
  public function update(Request $request, $id)
  {
    $user = Auth::user();
    // normalize time formats submitted by the client (allow 13.00 -> 13:00)
    $input = $request->all();
    $normalizeTime = function ($t) {
      if ($t === null || $t === '') return null;
      $t = str_replace('.', ':', $t);
      $t = preg_replace('/^(\d{1,2}:\d{2}).*/', '$1', $t);
      if (preg_match('/^\d:\d{2}$/', $t)) $t = '0' . $t;
      return $t;
    };

    if (!empty($input['jam_mulai'])) {
      $input['jam_mulai'] = $normalizeTime($input['jam_mulai']);
    }
    if (!empty($input['schedules']) && is_array($input['schedules'])) {
      foreach ($input['schedules'] as $k => $s) {
        if (!empty($s['jam_mulai'])) {
          $input['schedules'][$k]['jam_mulai'] = $normalizeTime($s['jam_mulai']);
        }
      }
    }
    $request->merge($input);
    Log::info('TerapisPatientController@update merged input', ['input' => $request->all()]);

    $data = $request->validate([
      'user_id' => 'nullable|exists:users,id',
      'anak_didik_id' => 'required|exists:anak_didiks,id',
      'tanggal_mulai' => 'nullable|date',
      'status' => 'nullable|in:aktif,non-aktif',
      'jam_mulai' => 'nullable|date_format:H:i',
      'jenis_terapi' => 'nullable|string|max:191',
      'terapis_nama' => 'nullable|string|max:191',
      'schedules' => 'nullable|array',
      'schedules.*.tanggal_mulai' => 'nullable|date',
      'schedules.*.jam_mulai' => 'nullable|date_format:H:i',
    ]);

    $assignment = GuruAnakDidik::findOrFail($id);
    Log::info('TerapisPatientController@update called', ['id' => $id, 'request' => $request->all()]);
    $terapisId = $user->role === 'terapis' ? $user->id : ($data['user_id'] ?? $assignment->user_id);

    $assignment->update([
      'user_id' => $terapisId,
      'anak_didik_id' => $data['anak_didik_id'],
      'status' => $data['status'] ?? $assignment->status,
      'tanggal_mulai' => $data['tanggal_mulai'] ?? $assignment->tanggal_mulai,
      'jam_mulai' => $data['jam_mulai'] ?? $assignment->jam_mulai,
      'jenis_terapi' => $data['jenis_terapi'] ?? $assignment->jenis_terapi,
      'terapis_nama' => $data['terapis_nama'] ?? $assignment->terapis_nama,
    ]);

    Log::info('TerapisPatientController@update after assignment->update', ['id' => $id, 'assignment' => $assignment->toArray()]);

    // replace schedules
    $assignment->schedules()->delete();
    if (!empty($data['schedules']) && is_array($data['schedules'])) {
      foreach ($data['schedules'] as $s) {
        if (empty($s['tanggal_mulai']) && empty($s['jam_mulai'])) continue;
        try {
          GuruAnakDidikSchedule::create([
            'guru_anak_didik_id' => $assignment->id,
            'hari' => $s['hari'] ?? '',
            'tanggal_mulai' => $s['tanggal_mulai'] ?? null,
            'jam_mulai' => $s['jam_mulai'] ?? null,
          ]);
        } catch (\Throwable $ex) {
          Log::error('Failed to create schedule on update', ['error' => $ex->getMessage(), 'data' => $s]);
        }
      }
    }

    Log::info('TerapisPatientController@update finished', ['id' => $id, 'fresh' => $assignment->fresh()->toArray()]);

    return redirect()->route('terapis.pasien.index')->with('success', 'Data pasien berhasil diperbarui.');
  }

  /**
   * Destroy an assignment
   */
  public function destroy($id)
  {
    $user = Auth::user();
    $assignment = GuruAnakDidik::findOrFail($id);
    // only admin or the assigned terapis can delete
    if (!($user->role === 'admin' || ($user->role === 'terapis' && $assignment->user_id == $user->id))) {
      return back()->with('error', 'Anda tidak berhak menghapus penugasan ini.');
    }
    $assignment->delete();
    return redirect()->route('terapis.pasien.index')->with('success', 'Penugasan berhasil dihapus.');
  }
}
