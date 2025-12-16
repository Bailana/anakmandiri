<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('program_anak', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('anak_didik_id');
      $table->string('nama_program');
      $table->date('periode_mulai');
      $table->date('periode_selesai');
      $table->enum('status', ['aktif', 'selesai', 'nonaktif'])->default('aktif');
      $table->text('keterangan')->nullable();
      $table->timestamps();

      $table->foreign('anak_didik_id')->references('id')->on('anak_didiks')->onDelete('cascade');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('program_anak');
  }
};
