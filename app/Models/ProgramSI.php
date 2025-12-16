<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramSI extends Model
{
  protected $table = 'program_si';
  protected $fillable = [
    'anak_didik_id',
    'user_id',
    'kemampuan',
    'keterangan',
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
