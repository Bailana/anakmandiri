<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
  protected $fillable = [
    'anak_didik_id',
    'konsultan_id',
    'program_id',
    'kategori',
    'perkembangan',
    'aktivitas',
    'hasil_penilaian',
    'rekomendasi',
    'saran',
    'tanggal_assessment',
  ];

  protected $casts = [
    'tanggal_assessment' => 'date',
  ];

  public function anakDidik()
  {
    return $this->belongsTo(AnakDidik::class);
  }

  public function konsultan()
  {
    return $this->belongsTo(Konsultan::class);
  }

  public function program()
  {
    return $this->belongsTo(Program::class);
  }
}
