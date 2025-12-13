<?php

namespace Database\Seeders;

use App\Models\AnakDidik;
use App\Models\GuruAnakDidik;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssignGuruFokusSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Dapatkan user guru fokus
    $guruFokus = User::where('email', 'guru.fokus@example.com')->first();

    if (!$guruFokus) {
      $this->command->error('Guru Fokus tidak ditemukan. Jalankan GuruFokusSeeder terlebih dahulu.');
      return;
    }

    // Dapatkan semua anak didik
    $anakDidiks = AnakDidik::all();

    foreach ($anakDidiks as $anakDidik) {
      // Cek apakah anak didik sudah memiliki guru fokus
      $existingAssignment = GuruAnakDidik::where('anak_didik_id', $anakDidik->id)
        ->where('user_id', $guruFokus->id)
        ->first();

      if (!$existingAssignment) {
        // Assign guru fokus ke anak didik
        GuruAnakDidik::create([
          'user_id' => $guruFokus->id,
          'anak_didik_id' => $anakDidik->id,
          'status' => 'aktif',
          'tanggal_mulai' => now(),
          'catatan' => 'Guru fokus yang ditugaskan untuk anak didik ' . $anakDidik->nama,
        ]);
      }
    }

    $this->command->info('Guru fokus berhasil ditugaskan untuk ' . $anakDidiks->count() . ' anak didik.');
  }
}
