<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramWicara extends Model
{
  protected $table = 'program_wicara';
  protected $fillable = [
    'anak_didik_id',
    'kemampuan',
    'wawancara',
    'kemampuan_saat_ini',
    'saran_rekomendasi',
  ];
  protected $casts = [
    'kemampuan' => 'array',
  ];

  public function anakDidik()
  {
    return $this->belongsTo(AnakDidik::class);
  }

  public function konsultan()
  {
    return $this->belongsTo(\App\Models\Konsultan::class, 'konsultan_id');
  }
}
