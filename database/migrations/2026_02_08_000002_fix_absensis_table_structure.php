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
    // Drop tabel lama jika ada
    Schema::dropIfExists('absensis');

    // Buat tabel baru dengan struktur yang benar
    Schema::create('absensis', function (Blueprint $table) {
      $table->id();
      $table->foreignId('anak_didik_id')->constrained('anak_didiks')->cascadeOnDelete();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // guru yang mengabsensi
      $table->date('tanggal');
      $table->enum('status', ['hadir', 'izin', 'alfa'])->default('hadir');
      $table->text('keterangan')->nullable();
      $table->timestamps();

      $table->index('tanggal');
      $table->unique(['anak_didik_id', 'tanggal']);
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
