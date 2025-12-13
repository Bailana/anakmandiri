<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AnakDidik;

class AnakDidikSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::table('anak_didiks')->delete();
        $data = [];
        $names = [
            'Ahmad Rizki Pratama',
            'Siti Nurhaliza Dewi',
            'Budi Santoso Wijaya',
            'Rina Kusuma Sari',
            'Doni Handoko Putra',
            'Fajar Ramadhan',
            'Lestari Ayu Puspita',
            'Galih Prakoso',
            'Nadia Putri',
            'Rizal Maulana',
            'Dewi Sartika',
            'Bagus Saputra',
            'Intan Permata',
            'Yoga Pratama',
            'Citra Lestari'
        ];

        // Ambil semua guru fokus (karyawan dengan posisi Guru Fokus)
        $guruFokusList = \App\Models\Karyawan::where('posisi', 'Guru Fokus')->get();
        $guruFokusCount = $guruFokusList->count();
        $guruFokusIndex = 0;
        $guruFokusAssigned = array_fill(0, $guruFokusCount, 0);

        for ($i = 0; $i < 15; $i++) {
            // Cari guru fokus yang belum dapat 3 anak didik
            while ($guruFokusAssigned[$guruFokusIndex] >= 3) {
                $guruFokusIndex = ($guruFokusIndex + 1) % $guruFokusCount;
            }
            $guruFokusId = $guruFokusList[$guruFokusIndex]->id;
            $guruFokusAssigned[$guruFokusIndex]++;

            $data[] = [
                'nama' => $names[$i],
                'nis' => str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'jenis_kelamin' => ($i % 2 == 0) ? 'laki-laki' : 'perempuan',
                'tanggal_lahir' => '2015-' . str_pad(($i % 12) + 1, 2, '0', STR_PAD_LEFT) . '-' . str_pad(($i % 28) + 1, 2, '0', STR_PAD_LEFT),
                'tempat_lahir' => ['Jakarta', 'Bandung', 'Surabaya', 'Yogyakarta', 'Medan'][$i % 5],
                'alamat' => 'Jl. Contoh No. ' . ($i + 1) . ', ' . $names[$i],
                'no_telepon_orang_tua' => '08' . rand(1000000000, 9999999999),
                'no_kk' => strval(rand(1000000000000000, 9999999999999999)),
                'nik' => strval(rand(1000000000000000, 9999999999999999)),
                'no_akta_kelahiran' => strval(rand(100000000, 999999999)),
                'tinggi_badan' => rand(135, 150) + (rand(0, 9) / 10),
                'berat_badan' => rand(35, 50) + (rand(0, 9) / 10),
                'jumlah_saudara_kandung' => rand(0, 3),
                'anak_ke' => rand(1, 4),
                'tinggal_bersama' => 'Orang Tua',
                'pendidikan_terakhir' => 'TK',
                'asal_sekolah' => 'TK Contoh ' . $i,
                'tanggal_pendaftaran' => '2024-' . str_pad((($i % 12) + 1), 2, '0', STR_PAD_LEFT) . '-' . str_pad((($i % 28) + 1), 2, '0', STR_PAD_LEFT),
                'kk' => true,
                'ktp_orang_tua' => true,
                'akta_kelahiran' => true,
                'foto_anak' => true,
                'pemeriksaan_tes_rambut' => (bool)rand(0, 1),
                'anamnesa' => (bool)rand(0, 1),
                'tes_iq' => (bool)rand(0, 1),
                'pemeriksaan_dokter_lab' => (bool)rand(0, 1),
                'surat_pernyataan' => (bool)rand(0, 1),
                'guru_fokus_id' => $guruFokusId,
            ];
        }
        foreach ($data as $item) {
            AnakDidik::create($item);
        }
    }
}
