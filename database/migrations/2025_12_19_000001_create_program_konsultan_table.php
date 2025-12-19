<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
  {
    Schema::create('program_konsultan', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('konsultan_id')->nullable();
      $table->string('kode_program')->nullable();
      $table->string('nama_program')->nullable();
      $table->text('tujuan')->nullable();
      $table->text('aktivitas')->nullable();
      $table->timestamps();

      $table->foreign('konsultan_id')->references('id')->on('konsultans')->onDelete('set null');
    });
  }

  public function down()
  {
    Schema::dropIfExists('program_konsultan');
  }
};
