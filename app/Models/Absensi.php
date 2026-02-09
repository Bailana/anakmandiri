<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
  protected $table = 'absensis';

  protected $fillable = [
    'anak_didik_id',
    'user_id',
    'tanggal',
    'status',
    'kondisi_fisik',
    'jenis_tanda_fisik',
    'keterangan_tanda_fisik',
    'lokasi_luka',
    'foto_bukti',
    'waktu_foto',
    'signature_pengantar',
    'nama_pengantar',
    'keterangan',
    'waktu_jemput',
    'nama_penjemput',
    'foto_penjemput',
    'signature_penjemput',
    'keterangan_penjemput',
  ];

  protected $casts = [
    'tanggal' => 'date',
    'waktu_foto' => 'datetime',
    'waktu_jemput' => 'datetime',
    'lokasi_luka' => 'array',
    'foto_bukti' => 'array',
    'foto_penjemput' => 'array',
  ];

  public function anakDidik()
  {
    return $this->belongsTo(AnakDidik::class);
  }

  public function guru()
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  // Mapping untuk kondisi fisik
  public static function getJenisTandaFisikOptions()
  {
    return [
      'baik' => 'Baik',
      'lebam' => 'Lebam',
      'luka_gores' => 'Luka Gores',
      'luka_terbuka' => 'Luka Terbuka',
      'bengkak' => 'Bengkak',
      'ruam' => 'Ruam',
      'bekas_gigitan' => 'Bekas Gigitan',
      'luka_bakar' => 'Luka Bakar',
      'bekas_cakar' => 'Bekas Cakar',
      'luka_lama' => 'Luka Lama',
    ];
  }

  public function getJenisTandaFisikLabelAttribute()
  {
    if (!$this->jenis_tanda_fisik) {
      return '-';
    }

    // Handle comma-separated values
    if (strpos($this->jenis_tanda_fisik, ',') !== false) {
      $items = explode(',', $this->jenis_tanda_fisik);
      $labels = [];
      $options = self::getJenisTandaFisikOptions();

      foreach ($items as $item) {
        $item = trim($item);
        $labels[] = $options[$item] ?? $item;
      }

      return implode(', ', $labels);
    }

    // Handle single value
    $options = self::getJenisTandaFisikOptions();
    return $options[$this->jenis_tanda_fisik] ?? $this->jenis_tanda_fisik;
  }
}
