<?php

namespace Database\Seeders;

use App\Models\Karyawan;
use Illuminate\Database\Seeder;

class KaryawanSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $karyawans = [
      [
        'nama' => 'Terapis SI',
        'nik' => '3275025678902001',
        'nip' => '1981006',
        'jenis_kelamin' => 'perempuan',
        'tanggal_lahir' => '1991-04-10',
        'tempat_lahir' => 'Jakarta',
        'alamat' => 'Jl. Kesehatan No. 1, Jakarta',
        'no_telepon' => '081234567806',
        'email' => 'terapis.si@example.com',
        'posisi' => 'Terapis',
        'departemen' => 'Terapi',
        'status_kepegawaian' => 'tetap',
        'tanggal_bergabung' => '2022-01-10',
        'pendidikan_terakhir' => 'S1',
        'institusi_pendidikan' => 'Universitas Indonesia',
        'keahlian' => 'Sensori Integrasi',
      ],
      [
        'nama' => 'Terapis Wicara',
        'nik' => '3275025678902002',
        'nip' => '1981007',
        'jenis_kelamin' => 'perempuan',
        'tanggal_lahir' => '1992-08-15',
        'tempat_lahir' => 'Bandung',
        'alamat' => 'Jl. Komunikasi No. 2, Bandung',
        'no_telepon' => '081234567807',
        'email' => 'terapis.wicara@example.com',
        'posisi' => 'Terapis',
        'departemen' => 'Terapi',
        'status_kepegawaian' => 'tetap',
        'tanggal_bergabung' => '2022-02-20',
        'pendidikan_terakhir' => 'S1',
        'institusi_pendidikan' => 'Universitas Padjadjaran',
        'keahlian' => 'Wicara',
      ],
      [
        'nama' => 'Terapis Perilaku',
        'nik' => '3275025678902003',
        'nip' => '1981008',
        'jenis_kelamin' => 'laki-laki',
        'tanggal_lahir' => '1990-12-01',
        'tempat_lahir' => 'Surabaya',
        'alamat' => 'Jl. Perilaku No. 3, Surabaya',
        'no_telepon' => '081234567808',
        'email' => 'terapis.perilaku@example.com',
        'posisi' => 'Terapis',
        'departemen' => 'Terapi',
        'status_kepegawaian' => 'tetap',
        'tanggal_bergabung' => '2022-03-15',
        'pendidikan_terakhir' => 'S1',
        'institusi_pendidikan' => 'Universitas Airlangga',
        'keahlian' => 'Perilaku',
      ],

      [
        'nama' => 'Budi Santoso',
        'nik' => '3275021234567890',
        'nip' => '1980001',
        'jenis_kelamin' => 'laki-laki',
        'tanggal_lahir' => '1985-06-15',
        'tempat_lahir' => 'Jakarta',
        'alamat' => 'Jl. Merdeka No. 123, Jakarta',
        'no_telepon' => '081234567890',
        'email' => 'budi.santoso@example.com',
        'posisi' => 'Manager',
        'departemen' => 'Operasional',
        'status_kepegawaian' => 'tetap',
        'tanggal_bergabung' => '2015-01-10',
        'pendidikan_terakhir' => 'S1',
        'institusi_pendidikan' => 'Universitas Indonesia',
        'keahlian' => 'Manajemen, Leadership',
      ],
      [
        'nama' => 'Siti Nurhaliza',
        'nik' => '3275022345678901',
        'nip' => '1980002',
        'jenis_kelamin' => 'perempuan',
        'tanggal_lahir' => '1988-03-22',
        'tempat_lahir' => 'Bandung',
        'alamat' => 'Jl. Sudirman No. 456, Bandung',
        'no_telepon' => '082345678901',
        'email' => 'siti.nurhaliza@example.com',
        'posisi' => 'Senior Developer',
        'departemen' => 'IT',
        'status_kepegawaian' => 'tetap',
        'tanggal_bergabung' => '2018-03-15',
        'pendidikan_terakhir' => 'S1',
        'institusi_pendidikan' => 'Institut Teknologi Bandung',
        'keahlian' => 'PHP, Laravel, JavaScript',
      ],
      [
        'nama' => 'Ahmad Wijaya',
        'nik' => '3275023456789012',
        'nip' => '1980003',
        'jenis_kelamin' => 'laki-laki',
        'tanggal_lahir' => '1990-09-10',
        'tempat_lahir' => 'Surabaya',
        'alamat' => 'Jl. Ahmad Yani No. 789, Surabaya',
        'no_telepon' => '083456789012',
        'email' => 'ahmad.wijaya@example.com',
        'posisi' => 'HR Specialist',
        'departemen' => 'Human Resources',
        'status_kepegawaian' => 'tetap',
        'tanggal_bergabung' => '2019-06-01',
        'pendidikan_terakhir' => 'S1',
        'institusi_pendidikan' => 'Universitas Airlangga',
        'keahlian' => 'Recruitment, Training',
      ],
      [
        'nama' => 'Dewi Lestari',
        'nik' => '3275024567890123',
        'nip' => '1980004',
        'jenis_kelamin' => 'perempuan',
        'tanggal_lahir' => '1992-12-05',
        'tempat_lahir' => 'Medan',
        'alamat' => 'Jl. Gatot Subroto No. 321, Medan',
        'no_telepon' => '084567890123',
        'email' => 'dewi.lestari@example.com',
        'posisi' => 'Marketing Executive',
        'departemen' => 'Marketing',
        'status_kepegawaian' => 'kontrak',
        'tanggal_bergabung' => '2020-09-15',
        'pendidikan_terakhir' => 'S1',
        'institusi_pendidikan' => 'Universitas Sumatera Utara',
        'keahlian' => 'Digital Marketing, SEO',
      ],
    ];

    foreach ($karyawans as $karyawan) {
      Karyawan::create($karyawan);
    }
  }
}
