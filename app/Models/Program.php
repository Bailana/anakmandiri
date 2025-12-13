<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
  protected $fillable = [
    'anak_didik_id',
    'konsultan_id',
    'nama_program',
    'deskripsi',
    'kategori',
    'target_pembelajaran',
    'tanggal_mulai',
    'tanggal_selesai',
    'catatan_konsultan',
    'is_approved',
  ];

  protected $casts = [
    'tanggal_mulai' => 'date',
    'tanggal_selesai' => 'date',
    'is_approved' => 'boolean',
  ];

  public function anakDidik()
  {
    return $this->belongsTo(AnakDidik::class);
  }

  public function konsultan()
  {
    return $this->belongsTo(Konsultan::class);
  }
}
