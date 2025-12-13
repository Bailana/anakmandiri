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
    Schema::create('assessments', function (Blueprint $table) {
      $table->id();
      $table->foreignId('anak_didik_id')->constrained('anak_didiks')->onDelete('cascade');
      $table->foreignId('konsultan_id')->nullable()->constrained('konsultans')->onDelete('set null');
      $table->enum('kategori', ['bina_diri', 'akademik', 'motorik', 'perilaku', 'vokasi']);
      $table->text('hasil_penilaian')->nullable();
      $table->text('rekomendasi')->nullable();
      $table->text('saran')->nullable();
      $table->date('tanggal_assessment');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('assessments');
  }
};
