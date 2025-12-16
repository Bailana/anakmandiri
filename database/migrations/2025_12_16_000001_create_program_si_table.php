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
    Schema::create('program_si', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('anak_didik_id');
      $table->unsignedBigInteger('user_id');
      $table->json('kemampuan')->nullable();
      $table->text('keterangan')->nullable();
      $table->timestamps();

      $table->foreign('anak_didik_id')->references('id')->on('anak_didiks')->onDelete('cascade');
      $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('program_si');
  }
};
