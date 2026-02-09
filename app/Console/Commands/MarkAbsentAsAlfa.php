<?php

namespace App\Console\Commands;

use App\Models\Absensi;
use App\Models\AnakDidik;
use Illuminate\Console\Command;

class MarkAbsentAsAlfa extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'absensi:mark-alfa';

  /**
   * The description of the console command.
   *
   * @var string
   */
  protected $description = 'Otomatis menandai siswa yang tidak ada absensi pada hari ini sebagai alfa';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    $hariIni = now()->toDateString();

    // Ambil semua anak didik yang aktif
    $semuaAnakDidik = AnakDidik::all();

    $count = 0;
    foreach ($semuaAnakDidik as $anak) {
      // Cek apakah sudah ada absensi untuk anak didik ini pada hari ini
      $absensi = Absensi::where('anak_didik_id', $anak->id)
        ->whereDate('tanggal', $hariIni)
        ->first();

      // Jika tidak ada absensi, buat record dengan status 'alfa'
      if (!$absensi) {
        Absensi::create([
          'anak_didik_id' => $anak->id,
          'user_id' => null,
          'tanggal' => $hariIni,
          'status' => 'alfa',
          'kondisi_fisik' => 'baik',
          'keterangan' => 'Otomatis terdata sebagai alfa karena tidak ada absensi hingga jam 4 sore',
        ]);
        $count++;
      }
    }

    $this->info("✓ Berhasil menandai {$count} siswa sebagai alfa");
  }
}
