<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnakDidik;
use App\Models\PpiItem;
use App\Models\Program;
use App\Models\Assessment;
use App\Models\Karyawan;
use App\Models\User;
use Carbon\Carbon;

class KedisiplinanController extends Controller
{
  public function index(Request $request)
  {
    // prepare two datasets:
    // - `rows` : penilaian utama (today) — should not change when user filters peringkat
    // - `rows_for_month` : peringkat berdasarkan pilihan bulan (if provided)
    $month = $request->query('month');
    // rows for penilaian view (always use today)
    $rowsToday = $this->buildRows(Carbon::today(), Carbon::today());
    // sort penilaian rows by guru name A-Z
    $rowsToday = collect($rowsToday)->sortBy(function ($r) {
      return $r->guru && isset($r->guru->nama) ? mb_strtolower($r->guru->nama) : '';
    })->values()->all();

    // rows for peringkat: if month provided, use that month range, otherwise default to current month
    if ($month) {
      try {
        $rangeStart = Carbon::parse($month . '-01')->startOfMonth();
        $rangeEnd = $rangeStart->copy()->endOfMonth();
      } catch (\Exception $e) {
        $rangeStart = Carbon::now()->startOfMonth();
        $rangeEnd = $rangeStart->copy()->endOfMonth();
      }
    } else {
      $rangeStart = Carbon::now()->startOfMonth();
      $rangeEnd = $rangeStart->copy()->endOfMonth();
    }

    $rowsForMonth = $this->buildRows($rangeStart, $rangeEnd);

    // focused debug: return raw assessments for a specific guru within the range when ?debug_guru=ID
    if ($request->query('debug_guru')) {
      $gid = (int) $request->query('debug_guru');
      $anakIds = AnakDidik::where('guru_fokus_id', $gid)->pluck('id')->toArray();
      $raw = [];
      if (!empty($anakIds)) {
        $qry = Assessment::with(['program', 'anakDidik', 'user', 'konsultan'])->whereIn('anak_didik_id', $anakIds);
        if ($rangeStart->toDateString() !== $rangeEnd->toDateString()) {
          $qry->whereBetween('tanggal_assessment', [$rangeStart->toDateString(), $rangeEnd->toDateString()]);
        } else {
          $qry->whereDate('tanggal_assessment', $rangeStart->toDateString());
        }
        $raw = $qry->orderBy('tanggal_assessment')->orderBy('created_at')->get()->map(function ($a) {
          return [
            'id' => $a->id ?? null,
            'anak_id' => $a->anak_didik_id ?? null,
            'anak_nama' => $a->anakDidik ? $a->anakDidik->nama : null,
            'program_nama' => $a->program && $a->program->nama_program ? $a->program->nama_program : null,
            'hasil_penilaian' => $a->hasil_penilaian ?? null,
            'aktivitas' => $a->aktivitas ?? null,
            'tanggal_assessment' => isset($a->tanggal_assessment) ? (string)$a->tanggal_assessment : null,
            'created_at' => isset($a->created_at) ? (string)$a->created_at : null,
            'penilai' => isset($a->user) ? $a->user->name : (isset($a->konsultan) ? $a->konsultan->nama : null),
          ];
        });
      }
      return response()->json(['guru_id' => $gid, 'anak_ids' => $anakIds, 'assessments' => $raw]);
    }

    // debug: return rows_for_month raw as JSON when ?debug=1 is provided
    if (request()->query('debug')) {
      return response()->json(['rows_for_month' => $rowsForMonth]);
    }

    return view('content.kedisiplinan.index', [
      'rows' => $rowsToday,
      'rows_for_month' => $rowsForMonth,
      'title' => 'Kedisiplinan',
    ]);
  }

  /**
   * Build rows dataset for a given date range (inclusive) used by both views
   */
  private function buildRows($rangeStart, $rangeEnd)
  {
    // Group anak didik by guru_fokus_id
    $anakDidiks = AnakDidik::whereNotNull('guru_fokus_id')->with('guruFokus')->get();
    $grouped = [];
    foreach ($anakDidiks as $anak) {
      $gid = $anak->guru_fokus_id;
      if (!isset($grouped[$gid])) $grouped[$gid] = [];
      $grouped[$gid][] = $anak;
    }

    $rows = [];
    foreach ($grouped as $gid => $anakList) {
      $guru = Karyawan::find($gid);

      // collect unique wajib program names across semua anak guru ini
      $namesSet = [];
      $anakIds = [];
      foreach ($anakList as $anak) {
        $anakIds[] = $anak->id;
        $items = PpiItem::whereHas('ppi', function ($q) use ($anak) {
          $q->where('anak_didik_id', $anak->id);
        })->where('aktif', 1)->get();
        foreach ($items as $it) {
          $n = trim(strtolower($it->nama_program ?? ''));
          if ($n !== '') $namesSet[$n] = true;
        }
      }

      $names = array_keys($namesSet);
      // keep unique names for matching, but compute total instances across children
      // i.e. sum of each child's wajib program count (do not deduplicate same program across different children)
      $totalWajib = count($names);
      $totalWajibInstances = 0;

      // fetch assessments within target range for these anakIds
      // keep `created_at` for determining the time of the assessment (on-time vs late)
      $assessmentsToday = [];
      if (!empty($anakIds)) {
        $qry = Assessment::with(['program', 'user', 'konsultan'])->whereIn('anak_didik_id', $anakIds);
        if ($rangeStart->toDateString() !== $rangeEnd->toDateString()) {
          $qry->whereBetween('tanggal_assessment', [$rangeStart->toDateString(), $rangeEnd->toDateString()]);
        } else {
          $qry->whereDate('tanggal_assessment', $rangeStart->toDateString());
        }
        $assessmentsToday = $qry->get();
      }

      // determine which wajib names have been assessed today and earliest time
      $matchedNames = [];
      $earliest = [];
      // also build per-anak map of earliest assessment per program name
      $byAnak = [];
      foreach ($assessmentsToday as $a) {
        $matchedName = null;
        if ($a->program && $a->program->nama_program) {
          $pn = trim(strtolower($a->program->nama_program));
          if (isset($namesSet[$pn])) $matchedName = $pn;
        }
        if (!$matchedName) {
          $hay = strtolower((string)($a->hasil_penilaian ?? '')) . ' ' . strtolower((string)($a->aktivitas ?? ''));
          foreach ($names as $nm) {
            if ($nm !== '' && strpos($hay, $nm) !== false) {
              $matchedName = $nm;
              break;
            }
          }
        }
        if ($matchedName) {
          $t = Carbon::parse($a->created_at);
          // store the earliest assessment object (by created_at) for this matched program name
          if (!isset($earliest[$matchedName]) || $t->lessThan(Carbon::parse($earliest[$matchedName]->created_at))) {
            $earliest[$matchedName] = $a;
          }
          $matchedNames[$matchedName] = true;
          $aid = $a->anak_didik_id ?? $a->anakDidik->id ?? null;
          if ($aid) {
            if (!isset($byAnak[$aid])) $byAnak[$aid] = [];
            if (!isset($byAnak[$aid][$matchedName]) || Carbon::parse($a->created_at)->lessThan(Carbon::parse($byAnak[$aid][$matchedName]->created_at))) {
              $byAnak[$aid][$matchedName] = $a;
            }
          }
        }
      }

      // count assessed instances per anak (if same program assessed for multiple children, count each)
      $doneCount = 0;
      foreach ($byAnak as $aidKey => $progMap) {
        $doneCount += is_array($progMap) ? count($progMap) : (is_object($progMap) ? count((array)$progMap) : 0);
      }

      // determine on-time vs late by scanning all matched assessments in the range
      $onTimePrograms = 0;
      $latePrograms = 0;
      $assessmentsByAnak = []; // group matched assessments per anak for per-anak counts
      foreach ($assessmentsToday as $a) {
        $matchedName = null;
        if ($a->program && $a->program->nama_program) {
          $pn = trim(strtolower($a->program->nama_program));
          if (isset($namesSet[$pn])) $matchedName = $pn;
        }
        if (!$matchedName) {
          $hay = strtolower((string)($a->hasil_penilaian ?? '')) . ' ' . strtolower((string)($a->aktivitas ?? ''));
          foreach ($names as $nm) {
            if ($nm !== '' && strpos($hay, $nm) !== false) {
              $matchedName = $nm;
              break;
            }
          }
        }
        if (!$matchedName) continue;
        $aid = $a->anak_didik_id ?? $a->anakDidik->id ?? null;
        if (!$aid) continue;

        if (!isset($assessmentsByAnak[$aid])) $assessmentsByAnak[$aid] = ['all' => [], 'on_time' => [], 'late' => []];
        $assessmentsByAnak[$aid]['all'][] = $a;

        $t = Carbon::parse($a->created_at);
        $assessDate = null;
        if (isset($a->tanggal_assessment) && $a->tanggal_assessment) {
          try {
            $assessDate = Carbon::parse($a->tanggal_assessment);
          } catch (\Exception $e) {
            $assessDate = null;
          }
        }
        // if tanggal_assessment exists but differs from created_at date, count as late
        if ($assessDate && $assessDate->toDateString() !== $t->toDateString()) {
          $latePrograms++;
          $assessmentsByAnak[$aid]['late'][] = $a;
        } else {
          $deadlineFor = $t->copy()->setTime(17, 0, 0);
          if ($t->lessThanOrEqualTo($deadlineFor)) {
            $onTimePrograms++;
            $assessmentsByAnak[$aid]['on_time'][] = $a;
          } else {
            $latePrograms++;
            $assessmentsByAnak[$aid]['late'][] = $a;
          }
        }
      }

      // score will be computed per-anak after we build $perAnak

      // compute per-anak breakdown (for ranking details)
      $perAnak = [];
      foreach ($anakList as $anak) {
        $aid = $anak->id;
        // fetch wajib program names for this anak
        $ppiItemsForAnak = PpiItem::whereHas('ppi', function ($q) use ($aid) {
          $q->where('anak_didik_id', $aid);
        })->where('aktif', 1)->get();
        $namesSetAnak = [];
        foreach ($ppiItemsForAnak as $it) {
          $n = trim(strtolower($it->nama_program ?? ''));
          if ($n !== '') $namesSetAnak[$n] = true;
        }
        $namesAnak = array_keys($namesSetAnak);
        $totalWajibAnak = count($namesAnak);
        $totalWajibInstances += $totalWajibAnak;
        // compute per-anak counts from matched assessments grouped earlier
        $assessedAnak = isset($assessmentsByAnak[$aid]) ? count($assessmentsByAnak[$aid]['all']) : 0;
        $onTimeAnak = isset($assessmentsByAnak[$aid]) ? count($assessmentsByAnak[$aid]['on_time']) : 0;
        $percent = $totalWajibAnak > 0 ? round($onTimeAnak / $totalWajibAnak * 100, 1) : 0;
        $perAnak[] = (object) [
          'anak_id' => $aid,
          'anak_nama' => $anak->nama ?? null,
          'total_wajib' => $totalWajibAnak,
          'assessed_count' => $assessedAnak,
          'on_time_count' => $onTimeAnak,
          'percent_on_time' => $percent,
        ];
      }

      // compute score per guru based on total on-time program assessments within range
      // each on-time program instance gives +10 points
      // apply penalty: each late program instance subtracts 15 points
      $score = (($onTimePrograms ?? 0) * 10) - (($latePrograms ?? 0) * 15);

      // Determine status. For single-day range (Penilaian tab) apply strict rules:
      // - If any required program for any child is not assessed that day -> 'Belum Dinilai'
      // - Else if any assessed program that day is late -> 'Terlambat'
      // - Else if all assessed programs that day are on-time -> 'Tepat Waktu'
      if ($rangeStart->toDateString() === $rangeEnd->toDateString()) {
        $hasMissing = false;
        foreach ($perAnak as $pa) {
          $total = $pa->total_wajib ?? 0;
          $assessed = $pa->assessed_count ?? 0;
          if ($total > 0 && $assessed < $total) {
            $hasMissing = true;
            break;
          }
        }
        if ($hasMissing) {
          $status = 'Belum Dinilai';
        } elseif (!empty($latePrograms)) {
          $status = 'Terlambat';
        } elseif (!empty($onTimePrograms)) {
          $status = 'Tepat Waktu';
        } else {
          $status = 'Belum Dinilai';
        }
      } else {
        // month-range behaviour (summary per month)
        $status = 'Belum Dinilai';
        if (!empty($onTimePrograms)) $status = 'Tepat Waktu';
        elseif (!empty($latePrograms)) $status = 'Terlambat';
      }

      // compute average assessment time (seconds since midnight) across matched mandatory assessments
      $timeSum = 0;
      $timeCount = 0;
      foreach ($byAnak as $aidKey => $progMap) {
        foreach ($progMap as $pname => $assessment) {
          if ($assessment && isset($assessment->created_at)) {
            $t = Carbon::parse($assessment->created_at);
            $seconds = $t->hour * 3600 + $t->minute * 60 + $t->second;
            $timeSum += $seconds;
            $timeCount++;
          }
        }
      }

      $avgTimeSeconds = null;
      $avgTimeDisplay = null;
      if ($timeCount > 0) {
        $avgTimeSeconds = (int) round($timeSum / $timeCount);
        $avgTimeDisplay = Carbon::createFromTime(0, 0, 0)->addSeconds($avgTimeSeconds)->format('H:i');
      }

      $rows[] = (object) [
        'guru' => $guru,
        // show total wajib as total instances across all children (so duplicates across different children are counted)
        'total_wajib' => $totalWajibInstances,
        'assessed_count' => $doneCount,
        'on_time_count' => $onTimePrograms,
        'late_count' => $latePrograms,
        'score' => $score,
        'avg_time_seconds' => $avgTimeSeconds,
        'avg_time' => $avgTimeDisplay,
        'status' => $status,
        'per_anak' => $perAnak,
      ];
    }

    return $rows;
  }

  /**
   * Return riwayat assessments (before today) for a given guru (grouped by date)
   */
  public function riwayat(Request $request, $guruId)
  {
    $anakIds = AnakDidik::where('guru_fokus_id', $guruId)->pluck('id')->toArray();
    if (empty($anakIds)) {
      return response()->json(['success' => true, 'riwayat' => []]);
    }

    // If date query provided, return assessments for that date only
    $date = $request->query('date');
    if (!$date) {
      // don't return all dates by default; require explicit date filter
      return response()->json(['success' => true, 'riwayat' => []]);
    }

    try {
      $dateParsed = Carbon::parse($date)->toDateString();
    } catch (\Exception $e) {
      return response()->json(['success' => false, 'message' => 'Invalid date']);
    }

    // build list of wajib program names across the guru's children (same logic as index)
    $namesSet = [];
    if (!empty($anakIds)) {
      $items = PpiItem::whereHas('ppi', function ($q) use ($anakIds) {
        $q->whereIn('anak_didik_id', $anakIds);
      })->where('aktif', 1)->get();
      foreach ($items as $it) {
        $n = trim(strtolower($it->nama_program ?? ''));
        if ($n !== '') $namesSet[$n] = true;
      }
    }

    $names = array_keys($namesSet);

    // select by assessment date (`tanggal_assessment`) but keep `created_at` for time
    $assessments = Assessment::with(['program', 'anakDidik', 'user', 'konsultan'])
      ->whereIn('anak_didik_id', $anakIds)
      ->whereDate('tanggal_assessment', $dateParsed)
      ->orderBy('created_at', 'asc')
      ->get();

    // Map program names to anak ids (which children have that program assigned)
    $ppiItems = PpiItem::whereHas('ppi', function ($q) use ($anakIds) {
      $q->whereIn('anak_didik_id', $anakIds);
    })->where('aktif', 1)->with('ppi')->get();

    $programToAnak = [];
    $nameDisplayMap = [];
    foreach ($ppiItems as $it) {
      $n = trim(strtolower($it->nama_program ?? ''));
      if ($n === '') continue;
      // preserve original casing for display when available
      if (!isset($nameDisplayMap[$n])) $nameDisplayMap[$n] = $it->nama_program;
      $aid = $it->ppi && isset($it->ppi->anak_didik_id) ? $it->ppi->anak_didik_id : null;
      if (!$aid) continue;
      if (!isset($programToAnak[$n])) $programToAnak[$n] = [];
      if (!in_array($aid, $programToAnak[$n])) $programToAnak[$n][] = $aid;
    }

    // map anak id to name
    $anakNames = AnakDidik::whereIn('id', $anakIds)->pluck('nama', 'id')->toArray();

    // build assessed map per program name and anak id (pick earliest per anak)
    $assessedMap = [];
    foreach ($assessments as $a) {
      $matchedName = null;
      if ($a->program && $a->program->nama_program) {
        $matchedName = trim(strtolower($a->program->nama_program));
        // preserve display name for programs that come from actual Program relation
        if (!isset($nameDisplayMap[$matchedName])) {
          $nameDisplayMap[$matchedName] = $a->program->nama_program;
        }
      }
      if (!$matchedName) {
        $hay = strtolower((string)($a->hasil_penilaian ?? '')) . ' ' . strtolower((string)($a->aktivitas ?? ''));
        foreach ($names as $nm) {
          if ($nm !== '' && strpos($hay, $nm) !== false) {
            $matchedName = $nm;
            break;
          }
        }
      }
      if ($matchedName) {
        $aid = $a->anak_didik_id;
        if (!isset($assessedMap[$matchedName])) $assessedMap[$matchedName] = [];
        if (!isset($assessedMap[$matchedName][$aid]) || Carbon::parse($a->created_at)->lessThan(Carbon::parse($assessedMap[$matchedName][$aid]->created_at))) {
          $assessedMap[$matchedName][$aid] = $a;
        }
      }
    }

    $items = [];
    $deadline = Carbon::parse($dateParsed)->setTime(17, 0, 0);

    // include all programs: wajib names + any programs that were actually assessed (even if non-wajib)
    $allProgramNames = array_unique(array_merge($names, array_keys($assessedMap)));

    foreach ($allProgramNames as $nm) {
      $assignedAnak = $programToAnak[$nm] ?? [];
      $assessedAnakList = isset($assessedMap[$nm]) ? array_keys($assessedMap[$nm]) : [];
      $uids = array_values(array_unique(array_merge($assignedAnak, $assessedAnakList)));

      if (!empty($uids)) {
        foreach ($uids as $aid) {
          if (isset($assessedMap[$nm][$aid])) {
            $a = $assessedMap[$nm][$aid];
            $waktu = $a->created_at ? $a->created_at->toTimeString() : null;
            $status = ($a->created_at && Carbon::parse($a->created_at)->lessThanOrEqualTo($deadline)) ? 'Tepat Waktu' : 'Terlambat';
            $penilaiUserId = null;
            $penilaiNama = null;
            if (isset($a->user) && $a->user) {
              $penilaiUserId = $a->user->id;
              $penilaiNama = $a->user->name;
            } elseif (isset($a->konsultan) && $a->konsultan) {
              $penilaiUserId = $a->konsultan->user_id ?? null;
              $penilaiNama = $a->konsultan->nama ?? null;
            } elseif (isset($a->user_id) && $a->user_id) {
              $u = User::find($a->user_id);
              if ($u) {
                $penilaiUserId = $u->id;
                $penilaiNama = $u->name;
              }
            }
            $items[] = [
              'anak' => $a->anakDidik ? $a->anakDidik->nama : ($anakNames[$aid] ?? null),
              'program' => $nm,
              'program_display' => $a->program && $a->program->nama_program ? $a->program->nama_program : ($nameDisplayMap[$nm] ?? $nm),
              'waktu' => $waktu,
              'status' => $status,
              'penilai_user_id' => $penilaiUserId,
              'penilai_nama' => $penilaiNama,
            ];
          } else {
            $items[] = [
              'anak' => $anakNames[$aid] ?? null,
              'program' => $nm,
              'program_display' => $nameDisplayMap[$nm] ?? $nm,
              'waktu' => null,
              'status' => 'Belum Dinilai',
              'penilai_user_id' => null,
              'penilai_nama' => null,
            ];
          }
        }
      } else {
        // fallback: no assigned anak and no per-anak assessed entries
        if (isset($assessedMap[$nm]) && count($assessedMap[$nm]) > 0) {
          // show each assessed entry (multiple anak)
          foreach ($assessedMap[$nm] as $aid => $first) {
            $waktu = $first->created_at ? $first->created_at->toTimeString() : null;
            $status = ($first->created_at && Carbon::parse($first->created_at)->lessThanOrEqualTo($deadline)) ? 'Tepat Waktu' : 'Terlambat';
            $penilaiUserId = null;
            $penilaiNama = null;
            if (isset($first->user) && $first->user) {
              $penilaiUserId = $first->user->id;
              $penilaiNama = $first->user->name;
            } elseif (isset($first->konsultan) && $first->konsultan) {
              $penilaiUserId = $first->konsultan->user_id ?? null;
              $penilaiNama = $first->konsultan->nama ?? null;
            } elseif (isset($first->user_id) && $first->user_id) {
              $u = User::find($first->user_id);
              if ($u) {
                $penilaiUserId = $u->id;
                $penilaiNama = $u->name;
              }
            }
            $items[] = [
              'anak' => $first->anakDidik ? $first->anakDidik->nama : null,
              'program' => $nm,
              'program_display' => $nameDisplayMap[$nm] ?? $nm,
              'waktu' => $waktu,
              'status' => $status,
              'penilai_user_id' => $penilaiUserId,
              'penilai_nama' => $penilaiNama,
            ];
          }
        } else {
          $items[] = [
            'anak' => null,
            'program' => $nm,
            'program_display' => $nameDisplayMap[$nm] ?? $nm,
            'waktu' => null,
            'status' => 'Belum Dinilai',
            'penilai_user_id' => null,
            'penilai_nama' => null,
          ];
        }
      }
    }

    return response()->json(['success' => true, 'riwayat' => [['date' => $dateParsed, 'items' => $items]]]);
  }
}
