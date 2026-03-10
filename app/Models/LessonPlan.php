<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonPlan extends Model
{
  protected $table = 'lesson_plans';

  protected $fillable = [
    'anak_didik_id',
    'ppi_id',
    'tanggal',
    'created_by',
  ];

  protected $casts = [
    'tanggal' => 'date',
  ];

  public function anak()
  {
    return $this->belongsTo(AnakDidik::class, 'anak_didik_id');
  }

  public function ppi()
  {
    return $this->belongsTo(Ppi::class, 'ppi_id');
  }

  public function schedules()
  {
    return $this->hasMany(LessonPlanSchedule::class, 'lesson_plan_id')->orderBy('urutan');
  }

  public function createdBy()
  {
    return $this->belongsTo(\App\Models\User::class, 'created_by');
  }
}
