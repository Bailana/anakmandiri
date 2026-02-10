<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramKonsultan extends Model
{
  protected $table = 'program_konsultan';
  protected $fillable = [
    'konsultan_id',
    'kode_program',
    'nama_program',
    'tujuan',
    'aktivitas',
    'keterangan',
    'deskripsi',
    'metode',
    'durasi',
  ];

  public function konsultan()
  {
    return $this->belongsTo(\App\Models\Konsultan::class, 'konsultan_id');
  }
}
