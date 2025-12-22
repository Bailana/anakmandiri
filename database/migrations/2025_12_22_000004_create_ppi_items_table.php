<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePpiItemsTable extends Migration
{
  public function up()
  {
    Schema::create('ppi_items', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('ppi_id');
      $table->string('nama_program')->nullable();
      $table->string('kategori')->nullable();
      $table->timestamps();

      $table->foreign('ppi_id')->references('id')->on('ppis')->onDelete('cascade');
    });
  }

  public function down()
  {
    Schema::dropIfExists('ppi_items');
  }
}
