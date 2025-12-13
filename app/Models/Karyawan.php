<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
  protected $fillable = [
    'nama',
    'nik',
    'nip',
    'jenis_kelamin',
    'tanggal_lahir',
    'tempat_lahir',
    'alamat',
    'no_telepon',
    'email',
    'posisi',
    'departemen',
    'status_kepegawaian',
    'tanggal_bergabung',
    'pendidikan_terakhir',
    'institusi_pendidikan',
    'keahlian',
    'foto_karyawan',
  ];

  protected $casts = [
    'tanggal_lahir' => 'date',
    'tanggal_bergabung' => 'date',
  ];
}
