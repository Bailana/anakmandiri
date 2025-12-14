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
    Schema::table('programs', function (Blueprint $table) {
      $table->json('kemampuan')->nullable()->after('kategori');
      $table->text('wawancara')->nullable()->after('kemampuan');
      $table->text('kemampuan_saat_ini')->nullable()->after('wawancara');
      $table->text('saran_rekomendasi')->nullable()->after('kemampuan_saat_ini');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('programs', function (Blueprint $table) {
      $table->dropColumn(['kemampuan', 'wawancara', 'kemampuan_saat_ini', 'saran_rekomendasi']);
    });
  }
};
