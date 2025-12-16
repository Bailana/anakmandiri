<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Konsultan extends Model
{
  protected $fillable = [
    'nama',
    'nik',
    'jenis_kelamin',
    'tanggal_lahir',
    'tempat_lahir',
    'alamat',
    'no_telepon',
    'email',
    'spesialisasi',
    'bidang_keahlian',
    'sertifikasi',
    'pengalaman_tahun',
    'status_hubungan',
    'tanggal_registrasi',
    'pendidikan_terakhir',
    'institusi_pendidikan',
    'foto_konsultan',
  ];

  protected $casts = [
    'tanggal_lahir' => 'date',
    'tanggal_registrasi' => 'date',
  ];

  public function user()
  {
    return $this->belongsTo(\App\Models\User::class, 'user_id');
  }
}
