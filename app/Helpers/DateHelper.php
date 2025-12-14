<?php

namespace App\Helpers;

class DateHelper
{
  public static function hariTanggal($tanggal)
  {
    $hari = [
      'Minggu',
      'Senin',
      'Selasa',
      'Rabu',
      'Kamis',
      'Jumat',
      'Sabtu'
    ];
    $date = date_create($tanggal);
    $dayIndex = (int)date_format($date, 'w');
    $hariStr = $hari[$dayIndex];
    $tglStr = date_format($date, 'd-m-Y');
    return [$hariStr, $tglStr];
  }
}
