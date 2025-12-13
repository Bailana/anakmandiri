<?php

namespace Database\Seeders;

use App\Models\Karyawan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GuruFokusSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $guruFokusList = [
      [
        'name' => 'Ayu Lestari',
        'email' => 'ayu.lestari@gurufokus.com',
        'jenis_kelamin' => 'perempuan',
        'tanggal_lahir' => '1992-03-12',
        'tempat_lahir' => 'Bandung',
        'alamat' => 'Jl. Melati No. 10, Bandung',
        'no_telepon' => '081234567801',
        'nip' => '1981001',
        'nik' => '3275025678901001',
        'pendidikan_terakhir' => 'S1',
        'institusi_pendidikan' => 'UPI Bandung',
        'keahlian' => 'Pendidikan Anak Usia Dini',
      ],
      [
        'name' => 'Budi Prakoso',
        'email' => 'budi.prakoso@gurufokus.com',
        'jenis_kelamin' => 'laki-laki',
        'tanggal_lahir' => '1990-06-25',
        'tempat_lahir' => 'Surabaya',
        'alamat' => 'Jl. Kenanga No. 22, Surabaya',
        'no_telepon' => '081234567802',
        'nip' => '1981002',
        'nik' => '3275025678901002',
        'pendidikan_terakhir' => 'S1',
        'institusi_pendidikan' => 'Unesa',
        'keahlian' => 'Matematika, Sains',
      ],
      [
        'name' => 'Citra Dewi',
        'email' => 'citra.dewi@gurufokus.com',
        'jenis_kelamin' => 'perempuan',
        'tanggal_lahir' => '1993-11-05',
        'tempat_lahir' => 'Yogyakarta',
        'alamat' => 'Jl. Anggrek No. 33, Yogyakarta',
        'no_telepon' => '081234567803',
        'nip' => '1981003',
        'nik' => '3275025678901003',
        'pendidikan_terakhir' => 'S1',
        'institusi_pendidikan' => 'UNY',
        'keahlian' => 'Bahasa Indonesia, Literasi',
      ],
      [
        'name' => 'Dian Saputra',
        'email' => 'dian.saputra@gurufokus.com',
        'jenis_kelamin' => 'laki-laki',
        'tanggal_lahir' => '1989-09-18',
        'tempat_lahir' => 'Semarang',
        'alamat' => 'Jl. Mawar No. 44, Semarang',
        'no_telepon' => '081234567804',
        'nip' => '1981004',
        'nik' => '3275025678901004',
        'pendidikan_terakhir' => 'S1',
        'institusi_pendidikan' => 'UNNES',
        'keahlian' => 'IPA, Keterampilan',
      ],
      [
        'name' => 'Eka Pratiwi',
        'email' => 'eka.pratiwi@gurufokus.com',
        'jenis_kelamin' => 'perempuan',
        'tanggal_lahir' => '1994-01-30',
        'tempat_lahir' => 'Medan',
        'alamat' => 'Jl. Flamboyan No. 55, Medan',
        'no_telepon' => '081234567805',
        'nip' => '1981005',
        'nik' => '3275025678901005',
        'pendidikan_terakhir' => 'S1',
        'institusi_pendidikan' => 'UNIMED',
        'keahlian' => 'Pendidikan Khusus, Bimbingan Konseling',
      ],
    ];

    foreach ($guruFokusList as $guru) {
      $user = User::create([
        'name' => $guru['name'],
        'email' => $guru['email'],
        'password' => Hash::make('password'),
        'role' => 'guru',
        'email_verified_at' => now(),
      ]);

      Karyawan::create([
        'nama' => $guru['name'],
        'nik' => $guru['nik'],
        'nip' => $guru['nip'],
        'jenis_kelamin' => $guru['jenis_kelamin'],
        'tanggal_lahir' => $guru['tanggal_lahir'],
        'tempat_lahir' => $guru['tempat_lahir'],
        'alamat' => $guru['alamat'],
        'no_telepon' => $guru['no_telepon'],
        'email' => $guru['email'],
        'posisi' => 'Guru Fokus',
        'departemen' => 'Pendidikan',
        'status_kepegawaian' => 'tetap',
        'tanggal_bergabung' => '2021-01-15',
        'pendidikan_terakhir' => $guru['pendidikan_terakhir'],
        'institusi_pendidikan' => $guru['institusi_pendidikan'],
        'keahlian' => $guru['keahlian'],
      ]);
    }
  }
}
