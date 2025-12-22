<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramAnak extends Model
{
  protected $table = 'program_anak';
  protected $fillable = [
    'anak_didik_id',
    'program_konsultan_id',
    'kode_program',
    'nama_program',
    'tujuan',
    'aktivitas',
    'periode_mulai',
    'periode_selesai',
    'status',
    'rekomendasi',
    'keterangan',
    'created_by',
    'created_by_name',
    'is_suggested',
  ];

  protected $casts = [
    'periode_mulai' => 'date',
    'periode_selesai' => 'date',
    'is_suggested' => 'boolean',
  ];

  public function anakDidik()
  {
    return $this->belongsTo(AnakDidik::class);
  }

  public function programKonsultan()
  {
    return $this->belongsTo(\App\Models\ProgramKonsultan::class, 'program_konsultan_id');
  }
}
