<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramAnak extends Model
{
  protected $table = 'program_anak';
  protected $fillable = [
    'anak_didik_id',
    'nama_program',
    'periode_mulai',
    'periode_selesai',
    'status',
    'keterangan',
  ];

  public function anakDidik()
  {
    return $this->belongsTo(AnakDidik::class);
  }
}
