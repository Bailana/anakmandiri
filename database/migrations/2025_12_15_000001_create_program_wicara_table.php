<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('program_wicara', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->unsignedBigInteger('anak_didik_id');
      $table->json('kemampuan')->nullable();
      $table->text('wawancara')->nullable();
      $table->text('kemampuan_saat_ini')->nullable();
      $table->text('saran_rekomendasi')->nullable();
      $table->timestamps();

      // Foreign key constraint (optional, uncomment if anak_didik_id is a foreign key)
      // $table->foreign('anak_didik_id')->references('id')->on('anak_didiks')->onDelete('cascade');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('program_wicara');
  }
};
