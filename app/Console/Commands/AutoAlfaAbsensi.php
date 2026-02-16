<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AnakDidik;
use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Karyawan;

class AutoAlfaAbsensi extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'absensi:auto-alfa {--force : Bypass cutoff and force creation for testing}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Create automatic "alfa" absensi for students who have not been marked present by cutoff (17:30 WIB). Use --force to bypass cutoff for testing.';

  /**
   * Execute the console command.
   */
  public function handle(): int
  {
    $tz = 'Asia/Jakarta';
    try {
      $now = Carbon::now($tz);
      $today = $now->toDateString();
      $force = (bool) $this->option('force');

      Log::info('AutoAlfaAbsensi run start', ['now' => $now->toDateTimeString(), 'tz' => $tz, 'force' => $force]);

      // Safety: ensure command is executed at or after cutoff time
      $cutoff = Carbon::today($tz)->setTime(17, 30, 0);
      if (!$force && $now->lt($cutoff)) {
        $this->info('Current time is before cutoff; skipping auto-alfa.');
        Log::info('AutoAlfaAbsensi skipped before cutoff', ['now' => $now->toDateTimeString(), 'cutoff' => $cutoff->toDateTimeString()]);
        return 0;
      }

      $candidates = AnakDidik::whereNotNull('guru_fokus_id')
        ->where('status', 'aktif')
        ->get();
      Log::info('AutoAlfaAbsensi candidates fetched', ['count' => $candidates->count()]);
      // Determine system user id to store on auto-created records.
      // Set AUTO_ALFA_USER_ID in .env (e.g. the admin user id). If missing or invalid, fallback to NULL.
      $rawSystemUser = env('AUTO_ALFA_USER_ID', null);
      $systemUserId = is_numeric($rawSystemUser) ? intval($rawSystemUser) : null;
      if ($systemUserId) {
        $exists = User::where('id', $systemUserId)->exists();
        if (!$exists) {
          Log::warning('Configured AUTO_ALFA_USER_ID not found in users table; using NULL', ['configured' => $systemUserId]);
          $this->warn("AUTO_ALFA_USER_ID={$systemUserId} not found; auto-created records will use NULL user_id");
          $systemUserId = null;
        }
      }

      $createdCount = 0;
      foreach ($candidates as $cand) {
        // Determine user_id: prefer guru_fokus.user_id if present and valid
        $userIdToUse = null;
        if (!empty($cand->guru_fokus_id)) {
          $karyawan = Karyawan::find($cand->guru_fokus_id);
          if ($karyawan) {
            // Prefer explicit user_id on karyawan if valid
            if (!empty($karyawan->user_id) && User::where('id', $karyawan->user_id)->exists()) {
              $userIdToUse = $karyawan->user_id;
            } else {
              // Fallback: try match User by karyawan name (case-insensitive)
              $namaKaryawan = trim($karyawan->nama ?? '');
              if ($namaKaryawan !== '') {
                $matchedUser = User::whereRaw('LOWER(name) = ?', [strtolower($namaKaryawan)])->first();
                if ($matchedUser) {
                  $userIdToUse = $matchedUser->id;
                }
              }
            }
          }
        }

        // fallback to configured system user if guru_fokus has no valid user
        if (empty($userIdToUse) && !empty($systemUserId)) {
          $userIdToUse = $systemUserId;
        }

        // Use firstOrCreate to avoid duplicates; if an existing record is present but
        // status is not a final value (e.g. not 'hadir' or 'izin'), set it to 'alfa'.
        $absensi = Absensi::firstOrCreate(
          [
            'anak_didik_id' => $cand->id,
            'tanggal' => $today,
          ],
          [
            'user_id' => $userIdToUse,
            'status' => 'alfa',
            'keterangan' => 'Auto alfa: belum absen pada cutoff 17:30 WIB',
          ]
        );

        if ($absensi->wasRecentlyCreated) {
          $createdCount++;
          Log::info('Auto-created alfa absensi', ['anak_didik_id' => $cand->id, 'tanggal' => $today, 'user_id' => $userIdToUse]);
        } else {
          // If record exists, ensure we don't overwrite valid statuses like 'hadir' or 'izin'.
          $current = strtolower(trim((string) $absensi->status));
          if (!in_array($current, ['hadir', 'izin', 'alfa'])) {
            $absensi->status = 'alfa';
            $absensi->keterangan = $absensi->keterangan ?: 'Auto alfa: belum absen pada cutoff 17:30 WIB';
            $absensi->user_id = $absensi->user_id ?: $userIdToUse;
            $absensi->save();
            $createdCount++;
            Log::info('Auto-updated existing absensi to alfa', ['anak_didik_id' => $cand->id, 'tanggal' => $today, 'previous_status' => $current, 'user_id' => $absensi->user_id]);
          }
        }
      }

      $this->info("Auto-alfa completed. Created: {$createdCount} records.");
      return 0;
    } catch (\Exception $e) {
      Log::error('AutoAlfaAbsensi command failed: ' . $e->getMessage());
      $this->error('AutoAlfaAbsensi failed: ' . $e->getMessage());
      return 1;
    }
  }
}
