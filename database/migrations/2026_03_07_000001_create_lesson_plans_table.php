<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('lesson_plans', function (Blueprint $table) {
      $table->id();
      $table->foreignId('anak_didik_id')->constrained('anak_didiks')->cascadeOnDelete();
      $table->foreignId('ppi_id')->nullable()->constrained('ppis')->nullOnDelete();
      $table->date('tanggal');
      $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
      $table->timestamps();
    });

    Schema::create('lesson_plan_schedules', function (Blueprint $table) {
      $table->id();
      $table->foreignId('lesson_plan_id')->constrained('lesson_plans')->cascadeOnDelete();
      $table->enum('section', ['awal', 'inti', 'penutup']);
      $table->time('jam_mulai');
      $table->time('jam_selesai');
      $table->text('keterangan')->nullable();
      $table->unsignedSmallInteger('urutan')->default(0);
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('lesson_plan_schedules');
    Schema::dropIfExists('lesson_plans');
  }
};
