<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('guru_anak_didik_schedules', function (Blueprint $table) {
      $table->id();
      $table->foreignId('guru_anak_didik_id')->constrained('guru_anak_didik')->onDelete('cascade');
      $table->string('hari', 50); // e.g., Senin, Selasa
      $table->time('jam_mulai')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('guru_anak_didik_schedules');
  }
};
