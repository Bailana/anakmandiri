<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramPendidikan extends Model
{
    protected $table = 'program_pendidikan';

    protected $fillable = [
        'anak_didik_id',
        'konsultan_id',
        'kode_program',
        'nama_program',
        'tujuan',
        'aktivitas',
        'periode_mulai',
        'periode_selesai',
        'keterangan',
        'is_suggested',
        'created_by',
    ];

    public function anakDidik()
    {
        return $this->belongsTo(AnakDidik::class);
    }

    public function konsultan()
    {
        return $this->belongsTo(Konsultan::class);
    }
}
