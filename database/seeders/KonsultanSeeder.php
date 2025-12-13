<?php

namespace Database\Seeders;

use App\Models\Konsultan;
use Illuminate\Database\Seeder;

class KonsultanSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $konsultans = [
      [
        'nama' => 'Dr. Hendra Gunawan',
        'nik' => '3275031234567890',
        'jenis_kelamin' => 'laki-laki',
        'tanggal_lahir' => '1975-05-12',
        'tempat_lahir' => 'Yogyakarta',
        'alamat' => 'Jl. Borobudur No. 100, Yogyakarta',
        'no_telepon' => '085567890123',
        'email' => 'konsultan.pendidikan@example.com',
        'spesialisasi' => 'Pendidikan',
        'bidang_keahlian' => 'Pendidikan Khusus, Kurikulum Adaptif, Program Pembelajaran',
        'sertifikasi' => 'Special Education Expert',
        'pengalaman_tahun' => 20,
        'status_hubungan' => 'aktif',
        'tanggal_registrasi' => '2015-03-20',
        'pendidikan_terakhir' => 'S3',
        'institusi_pendidikan' => 'Universitas Gadjah Mada',
      ],
      [
        'nama' => 'Ria Kusuma',
        'nik' => '3275032345678901',
        'jenis_kelamin' => 'perempuan',
        'tanggal_lahir' => '1980-08-25',
        'tempat_lahir' => 'Jakarta',
        'alamat' => 'Jl. Rasuna Said No. 250, Jakarta',
        'no_telepon' => '086678901234',
        'email' => 'konsultan.wicara@example.com',
        'spesialisasi' => 'Wicara',
        'bidang_keahlian' => 'Speech Therapy, Language Development, Articulation',
        'sertifikasi' => 'Speech Therapist Certified',
        'pengalaman_tahun' => 15,
        'status_hubungan' => 'aktif',
        'tanggal_registrasi' => '2016-06-15',
        'pendidikan_terakhir' => 'S2',
        'institusi_pendidikan' => 'Universitas Pendidikan Indonesia',
      ],
      [
        'nama' => 'Bambang Hermanto',
        'nik' => '3275033456789012',
        'jenis_kelamin' => 'laki-laki',
        'tanggal_lahir' => '1978-11-30',
        'tempat_lahir' => 'Semarang',
        'alamat' => 'Jl. Imam Bonjol No. 567, Semarang',
        'no_telepon' => '087789012345',
        'email' => 'konsultan.psikologi@example.com',
        'spesialisasi' => 'Psikologi',
        'bidang_keahlian' => 'Psikologi Anak, Konseling, Tes IQ',
        'sertifikasi' => 'Psychologist Certified',
        'pengalaman_tahun' => 18,
        'status_hubungan' => 'aktif',
        'tanggal_registrasi' => '2014-09-10',
        'pendidikan_terakhir' => 'S2',
        'institusi_pendidikan' => 'Universitas Diponegoro',
      ],
      [
        'nama' => 'Indah Puspita',
        'nik' => '3275034567890123',
        'jenis_kelamin' => 'perempuan',
        'tanggal_lahir' => '1985-02-14',
        'tempat_lahir' => 'Malang',
        'alamat' => 'Jl. Ijen No. 789, Malang',
        'no_telepon' => '088890123456',
        'email' => 'konsultan.si@example.com',
        'spesialisasi' => 'Sensori Integrasi',
        'bidang_keahlian' => 'Sensory Integration, Occupational Therapy, Gross/Fine Motor',
        'sertifikasi' => 'Occupational Therapist Certified',
        'pengalaman_tahun' => 12,
        'status_hubungan' => 'aktif',
        'tanggal_registrasi' => '2018-01-20',
        'pendidikan_terakhir' => 'S2',
        'institusi_pendidikan' => 'Universitas Brawijaya',
      ],
    ];

    foreach ($konsultans as $konsultan) {
      Konsultan::create($konsultan);
    }
  }
}
