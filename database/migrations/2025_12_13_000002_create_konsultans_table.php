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
    Schema::create('konsultans', function (Blueprint $table) {
      $table->id();
      $table->string('nama');
      $table->string('nik')->nullable()->unique();
      $table->enum('jenis_kelamin', ['laki-laki', 'perempuan'])->nullable();
      $table->date('tanggal_lahir')->nullable();
      $table->string('tempat_lahir')->nullable();
      $table->text('alamat')->nullable();
      $table->string('no_telepon')->nullable();
      $table->string('email')->nullable()->unique();
      $table->string('spesialisasi')->nullable();
      $table->text('bidang_keahlian')->nullable();
      $table->text('sertifikasi')->nullable();
      $table->integer('pengalaman_tahun')->nullable();
      $table->enum('status_hubungan', ['aktif', 'non-aktif'])->nullable();
      $table->date('tanggal_registrasi')->nullable();
      $table->string('pendidikan_terakhir')->nullable();
      $table->string('institusi_pendidikan')->nullable();
      $table->string('foto_konsultan')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('konsultans');
  }
};
