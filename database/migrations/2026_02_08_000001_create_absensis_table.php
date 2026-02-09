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
    Schema::create('absensis', function (Blueprint $table) {
      $table->id();
      $table->foreignId('guru_anak_didik_id')->constrained('guru_anak_didik')->cascadeOnDelete();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // guru yang mengabsensi
      $table->date('tanggal');
      $table->enum('status', ['hadir', 'izin', 'alfa'])->default('hadir');
      $table->text('keterangan')->nullable();
      $table->timestamps();

      $table->index('tanggal');
      $table->unique(['guru_anak_didik_id', 'tanggal']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('absensis');
  }
};
