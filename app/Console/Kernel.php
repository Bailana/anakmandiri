<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
  /**
   * Define the application's command schedule.
   */
  protected function schedule(Schedule $schedule): void
  {
    // Jalankan command untuk menandai siswa sebagai alfa setiap hari kerja (Senin-Jumat) pada jam 17:30 WIB
    $schedule->command('absensi:auto-alfa')
      ->weekdays()
      ->at('17:30')
      ->timezone('Asia/Jakarta');
  }

  /**
   * Register the commands for the application.
   */
  protected function commands(): void
  {
    $this->load(__DIR__ . '/Commands');

    require base_path('routes/console.php');
  }
}
