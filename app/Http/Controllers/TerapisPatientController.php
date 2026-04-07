<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GuruAnakDidik;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\AnakDidik;
use App\Models\GuruAnakDidikSchedule;
use App\Models\Karyawan;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class TerapisPatientController extends Controller
{
  /**
   * Update jadwal terapi berdasarkan id
   */
  public function updateJadwal(Request $request, $id)
  {
    $user = Auth::user();
    if (!in_array($user->role, ['admin', 'terapis'])) {
      return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    $jadwal = GuruAnakDidikSchedule::find($id);
    if (!$jadwal) {
      return response()->json(['success' => false, 'message' => 'Jadwal tidak ditemukan'], 404);
    }
    $data = $request->validate([
      'tanggal_mulai' => 'nullable|date',
      'jam_mulai' => 'nullable|date_format:H:i',
      'terapis_nama' => 'nullable|string|max:191',
    ]);
    $jadwal->update($data);
    return response()->json(['success' => true, 'data' => $jadwal]);
  }
  /**
   * Hapus jadwal terapi berdasarkan id
   */
  public function hapusJadwal($id)
  {
    $user = Auth::user();
    if (!in_array($user->role, ['admin', 'terapis'])) {
      return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }
    $jadwal = GuruAnakDidikSchedule::find($id);
    if (!$jadwal) {
      return response()->json(['success' => false, 'message' => 'Jadwal tidak ditemukan'], 404);
    }
    $jadwal->delete();
    return response()->json(['success' => true]);
  }
  /**
   * Display list of patients for a terapis (or allow admin to view/select terapis).
   */
  public function index(Request $request)
  {
    $user = Auth::user();
    $selectedTherapisId = $request->query('user_id');
    $selectedStatus = $request->query('status');
    $search = $request->query('search');
    $therapists = collect();

    // Build therapist list (used for optional filtering by admin).
    $therapists = User::where('role', 'terapis')->get();

    $query = GuruAnakDidik::with(['anakDidik', 'user', 'schedules']);

    // Admin can browse all assignments and optionally filter by therapist.
    // Terapis users should only see their own assignments.
    if ($user->role === 'admin') {
      if ($selectedTherapisId) {
        $query->where('user_id', $selectedTherapisId);
      } else {
        $query->whereIn('user_id', $therapists->pluck('id')->toArray());
      }
    } else {
      $normalizedUserName = strtolower(trim((string) $user->name));
      $query->where(function ($q) use ($user, $normalizedUserName) {
        $q->where('user_id', $user->id)
          // Backward-compatibility for old rows created by Kepala Klinik
          // that stored therapist only in `terapis_nama`.
          ->orWhereRaw('LOWER(TRIM(COALESCE(terapis_nama, ""))) = ?', [$normalizedUserName])
          ->orWhereHas('schedules', function ($sq) use ($normalizedUserName) {
            $sq->whereRaw('LOWER(TRIM(COALESCE(terapis_nama, ""))) = ?', [$normalizedUserName]);
          });
      });
    }

    if ($request->filled('search')) {
      $query->whereHas('anakDidik', function ($q) use ($search) {
        $q->where('nama', 'like', "%{$search}%")
          ->orWhere('nis', 'like', "%{$search}%");
      });
    }

    if ($request->filled('status')) {
      $query->where('status', $request->query('status'));
    }

    $assignments = $query
      ->orderBy(
        AnakDidik::select('nama')
          ->whereColumn('anak_didiks.id', 'guru_anak_didik.anak_didik_id')
          ->limit(1),
        'asc'
      )
      ->orderBy('id', 'asc')
      ->get();

    // Show one row per child for all roles. This prevents legacy duplicate rows
    // when a child has more than one assignment record.
    $groupedAssignments = $assignments
      ->groupBy('anak_didik_id')
      ->map(function ($items) {
        $base = clone $items->first();
        $jenisParts = [];
        $mergedSchedules = collect();

        foreach ($items as $item) {
          $raw = trim((string) ($item->jenis_terapi ?? ''));
          if ($raw !== '') {
            foreach (preg_split('/[|,]+/', $raw) as $part) {
              $part = trim($part);
              if ($part !== '') $jenisParts[] = $part;
            }
          }

          if ($item->relationLoaded('schedules')) {
            $mergedSchedules = $mergedSchedules->concat($item->schedules);
          }
        }

        $base->jenis_terapi = implode(' | ', array_values(array_unique($jenisParts)));
        $base->status = $items->contains(fn($item) => $item->status === 'aktif') ? 'aktif' : ($base->status ?? null);
        $base->setRelation('schedules', $mergedSchedules->unique('id')->values());
        return $base;
      })
      ->values();

    $perPage = 10;
    $currentPage = LengthAwarePaginator::resolveCurrentPage();
    $currentItems = $groupedAssignments->slice(($currentPage - 1) * $perPage, $perPage)->values();
    $assignments = new LengthAwarePaginator(
      $currentItems,
      $groupedAssignments->count(),
      $perPage,
      $currentPage,
      ['path' => $request->url(), 'query' => $request->query()]
    );

    return view('content.terapis.patients', compact('assignments', 'therapists', 'user', 'selectedTherapisId', 'selectedStatus'));
  }

  /**
   * Show form to assign a patient to a terapis
   */
  public function create(Request $request)
  {
    $user = Auth::user();
    if (!$this->canManagePatients($user)) {
      return back()->with('error', 'Anda tidak berhak menambah pasien terapis.');
    }
    // always provide list of therapists to view (for dropdowns)
    $therapists = User::where('role', 'terapis')->get();
    $selectedTherapisId = $user->role === 'terapis' ? $user->id : $request->query('user_id');

    $isKepalaTerapis = false;
    if ($user->role === 'terapis') {
      $isKepalaTerapis = Karyawan::where('email', $user->email)->where('posisi', 'Kepala Klinik')->exists()
        || Karyawan::where('nama', $user->name)->where('posisi', 'Kepala Klinik')->exists();
    }

    $anakDidiks = AnakDidik::orderBy('nama')->get();
    return view('content.terapis.create_patient', compact('therapists', 'user', 'selectedTherapisId', 'anakDidiks', 'isKepalaTerapis'));
  }

  /**
   * Show edit form for an existing assignment
   */
  public function edit($id)
  {
    $user = Auth::user();
    if (!$this->canManagePatients($user)) {
      return back()->with('error', 'Anda tidak berhak mengedit penugasan ini.');
    }
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
    if (!$this->canManagePatients($user)) {
      return back()->withInput()->with('error', 'Anda tidak berhak menambah pasien terapis.');
    }
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

    $terapisId = null;
    if ($user->role === 'terapis') {
      $terapisId = $user->id;
      // Kepala Klinik may assign to another therapist using `terapis_nama`.
      if ($this->isKepalaKlinik($user) && !empty($data['terapis_nama'])) {
        $mappedTherapistId = User::where('role', 'terapis')
          ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim((string) $data['terapis_nama']))])
          ->value('id');
        if (!empty($mappedTherapistId)) {
          $terapisId = $mappedTherapistId;
        }
      }
    } else {
      $terapisId = $data['user_id'] ?? null;
    }
    if (!$terapisId) {
      return back()->withInput()->withErrors(['user_id' => 'Pilih terapis.']);
    }

    // Check if an assignment already exists for this terapis + anak_didik.
    // The DB has a unique constraint on (user_id, anak_didik_id) so if an
    // assignment exists we must update/merge it instead of inserting.
    $existsQuery = GuruAnakDidik::where('anak_didik_id', $data['anak_didik_id']);
    if ($user->role === 'admin') {
      $existsQuery->where('user_id', $terapisId);
    } elseif (!$this->isKepalaKlinik($user)) {
      // Non-kepala therapist should attach new schedules to the existing child row.
      $existsQuery->where(function ($q) use ($terapisId) {
        $q->where('user_id', $terapisId)
          ->orWhereNull('user_id');
      });
    }

    $exists = $existsQuery->orderBy('id', 'asc')->first();
    if ($exists) {
      // DB has unique constraint on (user_id, anak_didik_id) so we cannot insert another
      // assignment row. Instead, update the existing assignment: merge jenis_terapi
      // values if different and append any provided schedules to it.
      $assignment = $exists;
      if ($user->role !== 'admin' && (int) $assignment->user_id !== (int) $terapisId) {
        $assignment->user_id = $terapisId;
      }
      $newJenis = $data['jenis_terapi'] ?? null;
      if ($newJenis) {
        $existingJenis = $assignment->jenis_terapi ?? '';
        if (trim($existingJenis) !== trim($newJenis)) {
          // merge unique jenis_terapi values
          $parts = array_filter(array_map('trim', explode('|', $existingJenis)));
          $parts[] = $newJenis;
          $parts = array_values(array_unique($parts));
          $assignment->jenis_terapi = implode(' | ', $parts);
        }
      }
      // do not overwrite assignment-level terapis_nama when merging to avoid
      // retroactively changing therapist name for existing schedules.
      if (empty($assignment->terapis_nama) && !empty($data['terapis_nama'])) {
        $assignment->terapis_nama = $data['terapis_nama'];
      }
      $assignment->tanggal_mulai = $data['tanggal_mulai'] ?? $assignment->tanggal_mulai;
      $assignment->jam_mulai = $data['jam_mulai'] ?? $assignment->jam_mulai;
      $assignment->status = $data['status'] ?? $assignment->status;
      $assignment->save();

      // store schedules if provided
      if (!empty($data['schedules']) && is_array($data['schedules'])) {
        foreach ($data['schedules'] as $s) {
          if (empty($s['tanggal_mulai']) && empty($s['jam_mulai'])) continue;
          try {
            GuruAnakDidikSchedule::create([
              'guru_anak_didik_id' => $assignment->id,
              'hari' => $s['hari'] ?? '',
              'tanggal_mulai' => $s['tanggal_mulai'] ?? null,
              'jam_mulai' => $s['jam_mulai'] ?? null,
              'jenis_terapi' => $s['jenis_terapi'] ?? ($data['jenis_terapi'] ?? null),
              'terapis_nama' => $data['terapis_nama'] ?? null,
            ]);
          } catch (\Throwable $ex) {
            // ignore individual schedule errors but continue
            Log::warning('Failed to create schedule while merging assignment', ['error' => $ex->getMessage(), 'data' => $s]);
          }
        }
      }

      return redirect()->route('terapis.pasien.index')->with('success', 'Jadwal terapi anak didik berhasil ditambahkan');
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
          'jenis_terapi' => $s['jenis_terapi'] ?? ($data['jenis_terapi'] ?? null),
          'terapis_nama' => $data['terapis_nama'] ?? null,
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
    if (!$this->canManagePatients($user)) {
      return back()->withInput()->with('error', 'Anda tidak berhak memperbarui penugasan ini.');
    }
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
    $terapisId = null;
    if ($user->role === 'terapis') {
      $terapisId = $user->id;
      // Kepala Klinik may reassign/edit records for another therapist.
      if ($this->isKepalaKlinik($user) && !empty($data['terapis_nama'])) {
        $mappedTherapistId = User::where('role', 'terapis')
          ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim((string) $data['terapis_nama']))])
          ->value('id');
        if (!empty($mappedTherapistId)) {
          $terapisId = $mappedTherapistId;
        }
      }
    } else {
      $terapisId = $data['user_id'] ?? $assignment->user_id;
    }

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
            'jenis_terapi' => $s['jenis_terapi'] ?? ($data['jenis_terapi'] ?? null),
            'terapis_nama' => $data['terapis_nama'] ?? null,
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
    if (!$this->canManagePatients($user)) {
      if (request()->expectsJson()) {
        return response()->json(['success' => false, 'message' => 'Anda tidak berhak menghapus penugasan ini.'], 403);
      }
      return back()->with('error', 'Anda tidak berhak menghapus penugasan ini.');
    }
    $assignment = GuruAnakDidik::find($id);
    if (!$assignment) {
      if (request()->expectsJson()) {
        return response()->json(['success' => false, 'message' => 'Penugasan tidak ditemukan.'], 404);
      }
      return back()->with('error', 'Penugasan tidak ditemukan.');
    }
    $assignment->delete();
    if (request()->expectsJson()) {
      return response()->json(['success' => true]);
    }
    return redirect()->route('terapis.pasien.index')->with('success', 'Penugasan berhasil dihapus.');
  }

  /**
   * Helper: apakah user boleh melakukan aksi manajemen pasien?
   * Admin selalu boleh. Terapis hanya jika tercatat sebagai 'Kepala Klinik' di tabel karyawans.
   */
  private function canManagePatients($user)
  {
    if (!$user) return false;
    if ($user->role === 'admin') return true;
    if ($user->role === 'terapis') return true;
    return false;
  }

  /**
   * Check if therapist account is marked as Kepala Klinik in karyawans table.
   */
  private function isKepalaKlinik($user): bool
  {
    if (!$user || $user->role !== 'terapis') return false;

    return Karyawan::where('email', $user->email)->where('posisi', 'Kepala Klinik')->exists()
      || Karyawan::where('nama', $user->name)->where('posisi', 'Kepala Klinik')->exists();
  }

  /**
   * Return JSON of therapy schedules for a given anak_didik across assignments
   */
  public function jadwalAnak($anakId)
  {
    $user = Auth::user();
    // only roles allowed by routes can access this; still, protect somewhat
    if (!in_array($user->role, ['admin', 'terapis'])) {
      return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    $assignments = GuruAnakDidik::with('schedules')
      ->where('anak_didik_id', $anakId)
      ->orderBy('jenis_terapi')
      ->get();

    $data = $assignments->map(function ($a) {
      return [
        'id' => $a->id,
        'jenis_terapi' => $a->jenis_terapi,
        'terapis_nama' => $a->terapis_nama,
        'status' => $a->status,
        'schedules' => $a->schedules->map(function ($s) use ($a) {
          return [
            'id' => $s->id,
            'tanggal_mulai' => $s->tanggal_mulai ? $s->tanggal_mulai->format('Y-m-d') : null,
            'jam_mulai' => $s->jam_mulai ? preg_replace('/^(\d{1,2}:\d{2}).*/', '$1', $s->jam_mulai) : null,
            'jenis_terapi' => $s->jenis_terapi ?? null,
            'terapis_nama' => $s->terapis_nama ?? $a->terapis_nama ?? null,
          ];
        })->values(),
      ];
    })->values();

    return response()->json(['success' => true, 'data' => $data]);
  }
}
