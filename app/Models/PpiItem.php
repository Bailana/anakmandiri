<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PpiItem extends Model
{
  use HasFactory;

  protected $table = 'ppi_items';

  protected $fillable = [
    'ppi_id',
    'nama_program',
    'kategori',
    'program_konsultan_id'
  ];

  public function programKonsultan()
  {
    return $this->belongsTo(\App\Models\ProgramKonsultan::class, 'program_konsultan_id');
  }

  public function ppi()
  {
    return $this->belongsTo(Ppi::class, 'ppi_id');
  }
}
