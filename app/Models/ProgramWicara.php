<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramWicara extends Model
{
  protected $table = 'program_wicara';
  protected $fillable = [
    'anak_didik_id',
    'user_id',
    'kemampuan',
    'wawancara',
    'kemampuan_saat_ini',
    'saran_rekomendasi',
    'diagnosa',
  ];
  protected $casts = [
    'kemampuan' => 'array',
  ];

  public function anakDidik()
  {
    return $this->belongsTo(AnakDidik::class);
  }

  public function user()
  {
    return $this->belongsTo(\App\Models\User::class, 'user_id');
  }
}
