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
    Schema::create('karyawans', function (Blueprint $table) {
      $table->id();
      $table->string('nama');
      $table->string('nik')->nullable()->unique();
      $table->string('nip')->nullable()->unique();
      $table->enum('jenis_kelamin', ['laki-laki', 'perempuan'])->nullable();
      $table->date('tanggal_lahir')->nullable();
      $table->string('tempat_lahir')->nullable();
      $table->text('alamat')->nullable();
      $table->string('no_telepon')->nullable();
      $table->string('email')->nullable()->unique();
      $table->string('posisi')->nullable();
      $table->string('departemen')->nullable();
      $table->enum('status_kepegawaian', ['tetap', 'kontrak', 'honorer'])->nullable();
      $table->date('tanggal_bergabung')->nullable();
      $table->string('pendidikan_terakhir')->nullable();
      $table->string('institusi_pendidikan')->nullable();
      $table->text('keahlian')->nullable();
      $table->string('foto_karyawan')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('karyawans');
  }
};
