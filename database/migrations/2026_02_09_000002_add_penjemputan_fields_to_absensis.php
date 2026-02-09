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
      $table->dateTime('waktu_jemput')->nullable()->after('waktu_foto');
      $table->string('nama_penjemput')->nullable()->after('waktu_jemput');
      $table->json('foto_penjemput')->nullable()->after('nama_penjemput');
      $table->text('signature_penjemput')->nullable()->after('foto_penjemput');
      $table->text('keterangan_penjemput')->nullable()->after('signature_penjemput');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('absensis', function (Blueprint $table) {
      $table->dropColumn([
        'waktu_jemput',
        'nama_penjemput',
        'foto_penjemput',
        'signature_penjemput',
        'keterangan_penjemput'
      ]);
    });
  }
};
