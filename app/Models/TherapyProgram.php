<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TherapyProgram extends Model
{
  protected $fillable = [
    'anak_didik_id',
    'type_therapy',
    'tanggal_mulai',
    'tanggal_selesai',
    'notes',
    'is_active',
  ];

  protected $casts = [
    'tanggal_mulai' => 'date',
    'tanggal_selesai' => 'date',
    'is_active' => 'boolean',
  ];

  public function anakDidik()
  {
    return $this->belongsTo(AnakDidik::class);
  }
}
