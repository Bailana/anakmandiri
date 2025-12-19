<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProgramKonsultan;
use App\Models\Konsultan;

class ProgramKonsultanSeeder extends Seeder
{
  public function run()
  {
    // if there are konsultan records, create sample programs for first few
    $konsultans = Konsultan::limit(5)->get();
    if ($konsultans->isEmpty()) return;

    foreach ($konsultans as $k) {
      // sample programs depending on spesialisasi
      $samples = [];
      $sp = strtolower($k->spesialisasi ?? '');
      if ($sp === 'wicara') {
        $samples = [
          ['kode_program' => 'WIC-001', 'nama_program' => 'Terapi ArtikulasI Dasar', 'tujuan' => 'Meningkatkan artikulasi suara', 'aktivitas' => 'Latihan pengucapan huruf vokal dan konsonan'],
          ['kode_program' => 'WIC-002', 'nama_program' => 'Peningkatan Bicara', 'tujuan' => 'Meningkatkan kelancaran bicara', 'aktivitas' => 'Latihan kalimat dan percakapan singkat'],
        ];
      } elseif ($sp === 'sensori integrasi') {
        $samples = [
          ['kode_program' => 'SI-001', 'nama_program' => 'Sensorimotor Dasar', 'tujuan' => 'Meningkatkan integrasi sensorik', 'aktivitas' => 'Latihan keseimbangan dan koordinasi'],
        ];
      } else {
        $samples = [
          ['kode_program' => 'GEN-001', 'nama_program' => 'Program Umum 1', 'tujuan' => 'Tujuan umum', 'aktivitas' => 'Aktivitas umum'],
        ];
      }

      foreach ($samples as $s) {
        ProgramKonsultan::create(array_merge($s, ['konsultan_id' => $k->id]));
      }
    }
  }
}
