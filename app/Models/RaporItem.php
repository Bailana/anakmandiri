<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RaporItem extends Model
{
    protected $fillable = [
        'rapor_id',
        'kategori',
        'nama_program',
        'nilai_huruf',
        'catatan',
    ];

    public function rapor()
    {
        return $this->belongsTo(Rapor::class);
    }
}
