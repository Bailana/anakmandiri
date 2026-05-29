<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rapor extends Model
{
    protected $fillable = [
        'anak_didik_id',
        'user_id',
        'kelas',
        'semester',
        'tahun_pelajaran',
        'group_notes',
        'saran_guru',
        'saran_orang_tua',
        'therapy_notes',
    ];

    protected $casts = [
        'group_notes' => 'array',
        'therapy_notes' => 'array',
    ];

    public function getGroupNotesAttribute($value)
    {
        if (!$value) {
            return [];
        }
        return is_array($value) ? $value : json_decode($value, true) ?? [];
    }

    public function anakDidik()
    {
        return $this->belongsTo(AnakDidik::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(RaporItem::class);
    }
}
