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
    // Jalankan command untuk menandai siswa sebagai alfa setiap hari pada jam 4 sore (16:00)
    $schedule->command('absensi:mark-alfa')
      ->dailyAt('16:00')
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
