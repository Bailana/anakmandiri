<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuruAnakDidikSchedule extends Model
{
  protected $table = 'guru_anak_didik_schedules';

  protected $fillable = [
    'guru_anak_didik_id',
    'hari',
    'tanggal_mulai',
    'jam_mulai',
    'jenis_terapi',
    'terapis_nama',
  ];

  protected $casts = [
    'jam_mulai' => 'string',
    'tanggal_mulai' => 'date',
  ];

  public function assignment()
  {
    return $this->belongsTo(GuruAnakDidik::class, 'guru_anak_didik_id');
  }
}
