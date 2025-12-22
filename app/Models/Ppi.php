<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ppi extends Model
{
  use HasFactory;

  protected $table = 'ppis';

  protected $fillable = [
    'anak_didik_id',
    'periode_mulai',
    'periode_selesai',
    'keterangan',
    'created_by',
    'status'
  ];

  public function items()
  {
    return $this->hasMany(PpiItem::class, 'ppi_id');
  }
}
