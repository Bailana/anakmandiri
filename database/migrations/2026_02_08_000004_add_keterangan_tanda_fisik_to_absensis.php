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
    Schema::table('absensis', function (Blueprint $table) {
      // Keterangan detail tentang tanda fisik yang ditemukan
      $table->text('keterangan_tanda_fisik')->nullable()->after('jenis_tanda_fisik')->comment('Deskripsikan kondisi/tanda fisik yang ditemukan');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('absensis', function (Blueprint $table) {
      $table->dropColumn('keterangan_tanda_fisik');
    });
  }
};
