<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonPlanSchedule extends Model
{
  protected $table = 'lesson_plan_schedules';

  protected $fillable = [
    'lesson_plan_id',
    'section',
    'jam_mulai',
    'jam_selesai',
    'keterangan',
    'nama_program',
    'urutan',
  ];

  public function lessonPlan()
  {
    return $this->belongsTo(LessonPlan::class, 'lesson_plan_id');
  }
}
