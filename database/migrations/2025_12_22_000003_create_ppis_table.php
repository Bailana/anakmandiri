<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePpisTable extends Migration
{
  public function up()
  {
    Schema::create('ppis', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('anak_didik_id');
      $table->date('periode_mulai');
      $table->date('periode_selesai');
      $table->text('keterangan')->nullable();
      $table->unsignedBigInteger('created_by')->nullable();
      $table->string('status')->nullable();
      $table->timestamps();

      $table->foreign('anak_didik_id')->references('id')->on('anak_didiks')->onDelete('cascade');
    });
  }

  public function down()
  {
    Schema::dropIfExists('ppis');
  }
}
