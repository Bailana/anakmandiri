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
    $deadline = Carbon::today()->setTime(17, 0, 0);

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
      $totalWajib = count($names);

      // fetch assessments whose assessment date (`tanggal_assessment`) is today for these anakIds
      // but keep `created_at` for determining the time of the assessment (on-time vs late)
      $assessmentsToday = [];
      if (!empty($anakIds)) {
        $assessmentsToday = Assessment::with(['program', 'user', 'konsultan'])
          ->whereIn('anak_didik_id', $anakIds)
          ->whereDate('tanggal_assessment', Carbon::today())
          ->get();
      }

      // determine which wajib names have been assessed today and earliest time
      $matchedNames = [];
      $earliest = [];
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
          if (!isset($earliest[$matchedName]) || $t->lessThan($earliest[$matchedName])) {
            $earliest[$matchedName] = $t;
          }
          $matchedNames[$matchedName] = true;
        }
      }

      $doneCount = count($matchedNames);

      // determine on-time vs late per matched name
      $onTimeCount = 0;
      $lateCount = 0;
      foreach ($earliest as $name => $time) {
        if ($time->lessThanOrEqualTo($deadline)) $onTimeCount++;
        else $lateCount++;
      }

      $status = 'Belum Dinilai';
      if ($totalWajib > 0 && $onTimeCount == $totalWajib) $status = 'Tepat Waktu';
      elseif ($lateCount > 0) $status = 'Terlambat';

      $rows[] = (object) [
        'guru' => $guru,
        'total_wajib' => $totalWajib,
        'assessed_count' => $doneCount,
        'status' => $status,
      ];
    }

    return view('content.kedisiplinan.index', [
      'rows' => $rows,
      'title' => 'Kedisiplinan',
    ]);
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
