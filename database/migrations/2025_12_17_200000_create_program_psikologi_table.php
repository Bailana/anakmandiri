<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up()
  {
    Schema::create('program_psikologi', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('anak_didik_id');
      $table->unsignedBigInteger('user_id');
      $table->json('kemampuan')->nullable();
      $table->text('wawancara')->nullable();
      $table->text('diagnosa')->nullable();
      $table->text('kemampuan_saat_ini')->nullable();
      $table->text('saran_rekomendasi')->nullable();
      $table->timestamps();

      $table->foreign('anak_didik_id')->references('id')->on('anak_didiks')->onDelete('cascade');
      $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });
  }

  public function down()
  {
    Schema::dropIfExists('program_psikologi');
  }
};
