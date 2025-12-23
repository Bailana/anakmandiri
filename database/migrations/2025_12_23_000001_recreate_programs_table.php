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
    if (!Schema::hasTable('programs')) {
      Schema::create('programs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('anak_didik_id')->constrained('anak_didiks')->onDelete('cascade');
        $table->foreignId('konsultan_id')->nullable()->constrained('konsultans')->onDelete('set null');
        $table->string('nama_program');
        $table->text('deskripsi')->nullable();
        $table->enum('kategori', ['bina_diri', 'akademik', 'motorik', 'perilaku', 'vokasi'])->nullable();
        $table->text('target_pembelajaran')->nullable();
        $table->date('tanggal_mulai')->nullable();
        $table->date('tanggal_selesai')->nullable();
        $table->text('catatan_konsultan')->nullable();
        $table->boolean('is_approved')->default(false)->comment('Approved by Konsultan Pendidikan');
        $table->json('kemampuan')->nullable();
        $table->text('wawancara')->nullable();
        $table->text('kemampuan_saat_ini')->nullable();
        $table->text('saran_rekomendasi')->nullable();
        $table->timestamps();
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('programs');
  }
};
