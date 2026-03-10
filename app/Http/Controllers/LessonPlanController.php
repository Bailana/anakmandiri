<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LessonPlan;
use App\Models\LessonPlanSchedule;
use App\Models\AnakDidik;
use App\Models\Ppi;
use App\Models\PpiItem;
use App\Models\ProgramKonsultan;
use App\Models\ProgramPendidikan;
use App\Models\Karyawan;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LessonPlanController extends Controller
{
  public function store(Request $request)
  {
    $request->validate([
      'anak_didik_id' => 'required|exists:anak_didiks,id',
      'ppi_id'        => 'required|exists:ppis,id',
      'tanggal'       => 'required|date',
      'schedules'     => 'nullable|array',
      'schedules.*.section'    => 'required|in:awal,inti,penutup',
      'schedules.*.jam_mulai'  => 'required|date_format:H:i',
      'schedules.*.jam_selesai' => 'required|date_format:H:i',
      'schedules.*.keterangan' => 'nullable|string|max:500',
    ]);

    $lp = LessonPlan::create([
      'anak_didik_id' => $request->anak_didik_id,
      'ppi_id'        => $request->ppi_id ?: null,
      'tanggal'       => $request->tanggal,
      'created_by'    => Auth::id(),
    ]);

    if ($request->has('schedules')) {
      $selectedPrograms = [];
      foreach ($request->schedules as $idx => $row) {
        // $idx is like 'awal_0', 'inti_1' — extract the numeric suffix for urutan
        $urutan = (int) substr($idx, strrpos($idx, '_') + 1);
        $namaProgram = null;
        if (isset($row['nama_program'])) {
          if (is_array($row['nama_program'])) {
            $programArr = array_filter($row['nama_program']);
            $namaProgram = implode(', ', $programArr);
            foreach ($programArr as $p) {
              $t = trim($p);
              if ($t !== '') $selectedPrograms[] = $t;
            }
          } else {
            $namaProgram = $row['nama_program'];
            $t = trim($row['nama_program']);
            if ($t !== '') $selectedPrograms[] = $t;
          }
        }
        LessonPlanSchedule::create([
          'lesson_plan_id' => $lp->id,
          'section'        => $row['section'],
          'jam_mulai'      => $row['jam_mulai'],
          'jam_selesai'    => $row['jam_selesai'],
          'keterangan'     => $row['keterangan'] ?? null,
          'nama_program'   => $namaProgram,
          'urutan'         => $urutan,
        ]);
      }

      // Mark selected programs as aktif in the linked PPI items
      if ($lp->ppi_id && !empty($selectedPrograms)) {
        PpiItem::where('ppi_id', $lp->ppi_id)
          ->whereIn('nama_program', $selectedPrograms)
          ->update(['aktif' => 1]);
      }
    }

    return redirect()->route('lesson-plan.index')->with('success', 'Lesson Plan berhasil dibuat.');
  }

  public function editJson($id)
  {
    $lp = LessonPlan::with(['ppi.items', 'schedules', 'anak'])->findOrFail($id);
    $user = Auth::user();

    $allowed = false;
    if ($user && $user->role === 'admin') {
      $allowed = true;
    } elseif ($user && $user->role === 'guru') {
      $k = Karyawan::where('nama', $user->name)->first();
      $kId = $k ? $k->id : null;
      if ($kId && $lp->anak && $lp->anak->guru_fokus_id == $kId) $allowed = true;
      if (!$allowed) $allowed = \App\Http\Controllers\GuruAnakDidikController::canAccessChild($user->id, $lp->anak_didik_id);
    }
    if (!$allowed) return response()->json(['success' => false], 403);

    $schedules = [];
    foreach (['awal', 'inti', 'penutup'] as $sec) {
      $schedules[$sec] = $lp->schedules
        ->where('section', $sec)
        ->sortBy('urutan')
        ->values()
        ->map(fn($r) => [
          'jam_mulai'    => Carbon::parse($r->jam_mulai)->format('H:i'),
          'jam_selesai'  => Carbon::parse($r->jam_selesai)->format('H:i'),
          'keterangan'   => $r->keterangan ?? '',
          'nama_program' => $r->nama_program
            ? array_values(array_filter(array_map('trim', explode(',', $r->nama_program))))
            : [],
        ])
        ->toArray();
    }

    $ppiPrograms = [];
    if ($lp->ppi) {
      $ppiPrograms = PpiItem::where('ppi_id', $lp->ppi_id)
        ->whereNotNull('nama_program')
        ->get(['nama_program'])
        ->map(fn($it) => ['nama' => trim($it->nama_program)])
        ->filter(fn($it) => $it['nama'] !== '')
        ->values()
        ->toArray();
    }

    return response()->json([
      'success'      => true,
      'lp_id'        => $lp->id,
      'tanggal'      => Carbon::parse($lp->tanggal)->format('Y-m'),
      'ppi_id'       => $lp->ppi_id,
      'ppi_programs' => $ppiPrograms,
      'schedules'    => $schedules,
    ]);
  }

  public function update(Request $request, $id)
  {
    $lp = LessonPlan::with('anak')->findOrFail($id);
    $user = Auth::user();

    $allowed = false;
    if ($user && $user->role === 'admin') {
      $allowed = true;
    } elseif ($user && $user->role === 'guru') {
      $k = Karyawan::where('nama', $user->name)->first();
      $kId = $k ? $k->id : null;
      if ($kId && $lp->anak && $lp->anak->guru_fokus_id == $kId) $allowed = true;
      if (!$allowed) $allowed = \App\Http\Controllers\GuruAnakDidikController::canAccessChild($user->id, $lp->anak_didik_id);
    }
    if (!$allowed) abort(403);

    $request->validate([
      'ppi_id'                   => 'required|exists:ppis,id',
      'tanggal'                  => 'required|date',
      'schedules'                => 'nullable|array',
      'schedules.*.section'      => 'required|in:awal,inti,penutup',
      'schedules.*.jam_mulai'    => 'required|date_format:H:i',
      'schedules.*.jam_selesai'  => 'required|date_format:H:i',
      'schedules.*.keterangan'   => 'nullable|string|max:500',
    ]);

    $lp->update([
      'ppi_id'  => $request->ppi_id ?: null,
      'tanggal' => $request->tanggal,
    ]);

    // Capture old ppi_id before schedules are deleted (for deactivation logic)
    $oldPpiId = $lp->getOriginal('ppi_id') ?: $lp->ppi_id;

    $lp->schedules()->delete();

    if ($request->has('schedules')) {
      $selectedPrograms = [];
      foreach ($request->schedules as $idx => $row) {
        $urutan = (int) substr($idx, strrpos($idx, '_') + 1);
        $namaProgram = null;
        if (isset($row['nama_program'])) {
          if (is_array($row['nama_program'])) {
            $programArr = array_filter($row['nama_program']);
            $namaProgram = implode(', ', $programArr);
            foreach ($programArr as $p) {
              $t = trim($p);
              if ($t !== '') $selectedPrograms[] = $t;
            }
          } else {
            $namaProgram = $row['nama_program'];
            $t = trim($row['nama_program']);
            if ($t !== '') $selectedPrograms[] = $t;
          }
        }
        LessonPlanSchedule::create([
          'lesson_plan_id' => $lp->id,
          'section'        => $row['section'],
          'jam_mulai'      => $row['jam_mulai'],
          'jam_selesai'    => $row['jam_selesai'],
          'keterangan'     => $row['keterangan'] ?? null,
          'nama_program'   => $namaProgram,
          'urutan'         => $urutan,
        ]);
      }
    } else {
      $selectedPrograms = [];
    }

    // Recalculate aktif status in ppi_items for the linked PPI
    $ppiIdToSync = $lp->ppi_id ?: $oldPpiId;
    if ($ppiIdToSync) {
      // Collect ALL programs still referenced by any lesson plan for this PPI
      $allActivePrograms = LessonPlan::where('ppi_id', $ppiIdToSync)
        ->with('schedules')
        ->get()
        ->flatMap(fn($l) => $l->schedules->pluck('nama_program'))
        ->filter()
        ->flatMap(fn($np) => array_filter(array_map('trim', explode(',', $np))))
        ->unique()
        ->values()
        ->toArray();

      // Mark programs present in any LP as aktif = 1, others as aktif = 0
      PpiItem::where('ppi_id', $ppiIdToSync)->update(['aktif' => 0]);
      if (!empty($allActivePrograms)) {
        PpiItem::where('ppi_id', $ppiIdToSync)
          ->whereIn('nama_program', $allActivePrograms)
          ->update(['aktif' => 1]);
      }
    }

    return redirect()->back()->with('success', 'Lesson Plan berhasil diperbarui.');
  }

  public function destroy($id)
  {
    $lp = LessonPlan::with('schedules')->findOrFail($id);
    $user = Auth::user();

    $allowed = false;
    if ($user && $user->role === 'admin') {
      $allowed = true;
    } elseif ($user && $user->role === 'guru') {
      $k = Karyawan::where('nama', $user->name)->first();
      $kId = $k ? $k->id : null;
      if ($kId && $lp->anak && $lp->anak->guru_fokus_id == $kId) $allowed = true;
      if (!$allowed) $allowed = \App\Http\Controllers\GuruAnakDidikController::canAccessChild($user->id, $lp->anak_didik_id);
    }
    if (!$allowed) abort(403);

    $ppiId = $lp->ppi_id;
    $lp->delete();

    // Recalculate aktif for all ppi_items in this PPI
    if ($ppiId) {
      $allActivePrograms = LessonPlan::where('ppi_id', $ppiId)
        ->with('schedules')
        ->get()
        ->flatMap(fn($l) => $l->schedules->pluck('nama_program'))
        ->filter()
        ->flatMap(fn($np) => array_filter(array_map('trim', explode(',', $np))))
        ->unique()
        ->values()
        ->toArray();

      PpiItem::where('ppi_id', $ppiId)->update(['aktif' => 0]);
      if (!empty($allActivePrograms)) {
        PpiItem::where('ppi_id', $ppiId)
          ->whereIn('nama_program', $allActivePrograms)
          ->update(['aktif' => 1]);
      }
    }

    return redirect()->back()->with('success', 'Lesson Plan berhasil dihapus.');
  }

  public function riwayat($anakId)
  {
    $anak = AnakDidik::findOrFail($anakId);
    $user = Auth::user();

    // Access check
    $allowed = false;
    if ($user && $user->role === 'admin') {
      $allowed = true;
    } elseif ($user && $user->role === 'guru') {
      $allowed = \App\Http\Controllers\GuruAnakDidikController::canAccessChild($user->id, $anakId);
      if (!$allowed) {
        $k = Karyawan::where('nama', $user->name)->first();
        if ($k && $anak->guru_fokus_id == $k->id) {
          $allowed = true;
        }
      }
    }
    if (!$allowed) abort(403);

    $lessonPlans = LessonPlan::with('ppi')
      ->where('anak_didik_id', $anakId)
      ->orderByDesc('tanggal')
      ->get();

    return view('content.lesson-plan.riwayat', compact('anak', 'lessonPlans'));
  }

  public function preview($id)
  {
    $lp = LessonPlan::with([
      'anak.guruFokus',
      'ppi',
      'schedules',
    ])->findOrFail($id);

    $user = Auth::user();

    // Access check
    $allowed = false;
    if ($user && $user->role === 'admin') {
      $allowed = true;
    } elseif ($user && $user->role === 'guru') {
      $k = Karyawan::where('nama', $user->name)->first();
      $kId = $k ? $k->id : null;
      if ($kId && $lp->anak && $lp->anak->guru_fokus_id == $kId) {
        $allowed = true;
      }
      if (!$allowed) {
        $allowed = \App\Http\Controllers\GuruAnakDidikController::canAccessChild($user->id, $lp->anak_didik_id);
      }
    }
    if (!$allowed) {
      abort(403);
    }

    $schedulesBySection = [
      'awal'    => $lp->schedules->where('section', 'awal')->values(),
      'inti'    => $lp->schedules->where('section', 'inti')->values(),
      'penutup' => $lp->schedules->where('section', 'penutup')->values(),
    ];

    $ppi = $lp->ppi;
    $anakDidik = $lp->anak;
    $periodeMulai   = $ppi && $ppi->periode_mulai   ? Carbon::parse($ppi->periode_mulai)->locale('id')->translatedFormat('F Y') : '-';
    $periodeSelesai = $ppi && $ppi->periode_selesai ? Carbon::parse($ppi->periode_selesai)->locale('id')->translatedFormat('F Y') : '-';

    // Build program data from active PPI items
    $programData = [];
    if ($ppi) {
      $activeItems = PpiItem::with('programKonsultan.konsultan')
        ->where('ppi_id', $ppi->id)
        ->where('aktif', 1)
        ->get();

      foreach ($activeItems as $item) {
        $pk = $item->programKonsultan;

        $dispKode = '-';
        $dispNama = $item->nama_program ?? '-';
        $dispKategori = $item->kategori ?? '-';
        $dispKonsultanNama = '-';
        $dispKonsultanSpec = '-';
        $dispTujuan = '-';
        $dispAktivitas = '-';
        $dispKeterangan = '-';

        if ($pk) {
          $dispKode = $pk->kode_program ?? '-';
          $dispNama = $pk->nama_program ?? $dispNama;
          $dispTujuan = $pk->tujuan ?? '-';
          $dispAktivitas = $pk->aktivitas ?? '-';
          $dispKeterangan = $pk->keterangan ?? '-';
          if ($pk->konsultan) {
            $dispKonsultanNama = $pk->konsultan->nama ?? '-';
            $dispKonsultanSpec = $pk->konsultan->spesialisasi ?? '-';
          }
        } else {
          $kode = null;
          $nm = (string)($item->nama_program ?? '');
          if (preg_match('/^([A-Za-z]{2,3})\s*-?\s*(\d{2,4})/i', $nm, $m)) {
            $kode = strtoupper($m[1] . $m[2]);
          }

          $pp = ProgramPendidikan::with('konsultan')
            ->where('anak_didik_id', $ppi->anak_didik_id)
            ->where(function ($q) use ($kode, $nm) {
              if ($kode) {
                $q->orWhereRaw("REPLACE(UPPER(kode_program),'-','') = ?", [str_replace('-', '', strtoupper($kode))]);
              }
              $san = strtolower(preg_replace('/[\s\.-]+/', '', $nm));
              if ($san !== '') {
                $q->orWhereRaw("REPLACE(REPLACE(REPLACE(LOWER(nama_program),' ',''),'.',''),'-','') = ?", [$san]);
              }
            })
            ->orderByDesc('id')
            ->first();

          if ($pp) {
            $dispKode = $pp->kode_program ?? '-';
            $dispNama = $pp->nama_program ?? $dispNama;
            $dispTujuan = $pp->tujuan ?? '-';
            $dispAktivitas = $pp->aktivitas ?? '-';
            $dispKeterangan = $pp->keterangan ?? '-';
            if ($pp->konsultan) {
              $dispKonsultanNama = $pp->konsultan->nama ?? '-';
              $dispKonsultanSpec = $pp->konsultan->spesialisasi ?? '-';
            }
          } elseif ($kode) {
            $mk = ProgramKonsultan::with('konsultan')
              ->whereRaw("REPLACE(UPPER(kode_program),'-','') = ?", [str_replace('-', '', strtoupper($kode))])
              ->first();
            if ($mk) {
              $dispKode = $mk->kode_program ?? '-';
              $dispNama = $mk->nama_program ?? $dispNama;
              $dispTujuan = $mk->tujuan ?? '-';
              $dispAktivitas = $mk->aktivitas ?? '-';
              $dispKeterangan = $mk->keterangan ?? '-';
              if ($mk->konsultan) {
                $dispKonsultanNama = $mk->konsultan->nama ?? '-';
                $dispKonsultanSpec = $mk->konsultan->spesialisasi ?? '-';
              }
            }
          }
        }

        $programData[] = [
          'kode_program'           => $dispKode,
          'nama_program'           => $dispNama,
          'kategori'               => $dispKategori,
          'konsultan_nama'         => $dispKonsultanNama,
          'konsultan_spesialisasi' => $dispKonsultanSpec,
          'tujuan'                 => $dispTujuan,
          'aktivitas'              => $dispAktivitas,
          'keterangan'             => $dispKeterangan,
          'notes'                  => $item->notes ?? null,
        ];
      }
    }

    // Build nama_program → kategori lookup for schedule badges
    $programCategories = [];
    if ($ppi) {
      PpiItem::where('ppi_id', $ppi->id)
        ->whereNotNull('nama_program')
        ->get(['nama_program', 'kategori'])
        ->each(function ($item) use (&$programCategories) {
          $programCategories[trim($item->nama_program)] = $item->kategori ?? '';
        });
    }

    return view('content.lesson-plan.preview', compact(
      'lp',
      'anakDidik',
      'ppi',
      'schedulesBySection',
      'periodeMulai',
      'periodeSelesai',
      'programData',
      'programCategories'
    ));
  }

  public function detailJson($id)
  {
    $lp = LessonPlan::with(['anak.guruFokus', 'ppi', 'schedules'])->findOrFail($id);
    $user = Auth::user();

    $allowed = false;
    if ($user && $user->role === 'admin') {
      $allowed = true;
    } elseif ($user && $user->role === 'guru') {
      $k = Karyawan::where('nama', $user->name)->first();
      $kId = $k ? $k->id : null;
      if ($kId && $lp->anak && $lp->anak->guru_fokus_id == $kId) $allowed = true;
      if (!$allowed) $allowed = \App\Http\Controllers\GuruAnakDidikController::canAccessChild($user->id, $lp->anak_didik_id);
    }
    if (!$allowed) return response()->json(['success' => false], 403);

    $schedulesBySection = [];
    foreach (['awal', 'inti', 'penutup'] as $sec) {
      $schedulesBySection[$sec] = $lp->schedules->where('section', $sec)->values()->map(function ($r) {
        return [
          'jam_mulai'    => Carbon::parse($r->jam_mulai)->format('H:i'),
          'jam_selesai'  => Carbon::parse($r->jam_selesai)->format('H:i'),
          'nama_program' => $r->nama_program,
          'keterangan'   => $r->keterangan,
        ];
      });
    }

    $ppi = $lp->ppi;
    return response()->json([
      'success'    => true,
      'ppi_id'     => $lp->ppi_id,
      'tanggal'    => Carbon::parse($lp->tanggal)->locale('id')->translatedFormat('F Y'),
      'anak'       => $lp->anak ? ['nama' => $lp->anak->nama, 'nis' => $lp->anak->nis, 'guru_fokus' => $lp->anak->guruFokus ? $lp->anak->guruFokus->nama : '-'] : null,
      'periode'    => ($ppi && $ppi->periode_mulai && $ppi->periode_selesai)
        ? Carbon::parse($ppi->periode_mulai)->locale('id')->translatedFormat('F Y') . ' s/d ' . Carbon::parse($ppi->periode_selesai)->locale('id')->translatedFormat('F Y')
        : null,
      'schedules'  => $schedulesBySection,
    ]);
  }

  public function programDetail(\Illuminate\Http\Request $request)
  {
    $namaProgram = $request->query('nama_program');
    $ppiId       = $request->query('ppi_id');

    if (!$namaProgram) {
      return response()->json(['success' => false, 'message' => 'Nama program wajib diisi'], 422);
    }

    // Find ppi_item matching nama_program; optionally scoped to a specific ppi
    $query = \App\Models\PpiItem::with('programKonsultan')
      ->where('nama_program', $namaProgram);
    if ($ppiId) {
      $query->where('ppi_id', $ppiId);
    }
    $item = $query->first();

    if (!$item) {
      return response()->json(['success' => true, 'program' => null, 'nama_program' => $namaProgram]);
    }

    $pk = $item->programKonsultan;
    return response()->json([
      'success'      => true,
      'nama_program' => $item->nama_program,
      'kategori'     => $item->kategori,
      'program' => $pk ? [
        'kode_program' => $pk->kode_program,
        'nama_program' => $pk->nama_program,
        'tujuan'       => $pk->tujuan,
        'aktivitas'    => $pk->aktivitas,
        'metode'       => $pk->metode ?? null,
        'durasi'       => $pk->durasi ?? null,
        'keterangan'   => $pk->keterangan,
        'deskripsi'    => $pk->deskripsi,
      ] : null,
    ]);
  }
}
