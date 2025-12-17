<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramPsikologi extends Model
{
  protected $table = 'program_psikologi';
  protected $fillable = [
    'anak_didik_id',
    'user_id',
    // removed: kemampuan, wawancara, diagnosa, kemampuan_saat_ini, saran_rekomendasi
    'latar_belakang',
    'metode_assessment',
    'hasil_assessment',
    'kesimpulan',
    'rekomendasi',
    'diagnosa_psikologi',
    'konsultan_id',
  ];
  protected $casts = [];

  public function anakDidik()
  {
    return $this->belongsTo(AnakDidik::class);
  }

  public function konsultan()
  {
    return $this->belongsTo(Konsultan::class, 'konsultan_id');
  }

  public function user()
  {
    return $this->belongsTo(\App\Models\User::class, 'user_id');
  }
}
