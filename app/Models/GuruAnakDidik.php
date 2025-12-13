<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuruAnakDidik extends Model
{
  protected $table = 'guru_anak_didik';

  protected $fillable = [
    'user_id',
    'anak_didik_id',
    'status',
    'tanggal_mulai',
    'tanggal_selesai',
    'catatan',
  ];

  protected $casts = [
    'tanggal_mulai' => 'date',
    'tanggal_selesai' => 'date',
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function anakDidik()
  {
    return $this->belongsTo(AnakDidik::class);
  }

  public function approvalRequests()
  {
    return $this->hasMany(GuruAnakDidikApproval::class, 'anak_didik_id', 'anak_didik_id');
  }
}
