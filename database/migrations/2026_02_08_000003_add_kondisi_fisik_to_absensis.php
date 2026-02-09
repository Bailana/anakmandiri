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
      // Kondisi fisik
      $table->enum('kondisi_fisik', ['baik', 'ada_tanda'])->default('baik')->after('status');

      // Detail tanda fisik
      $table->string('jenis_tanda_fisik')->nullable()->after('kondisi_fisik')->comment('baik, lebam, luka_gores, luka_terbuka, bengkak, ruam, bekas_gigitan, luka_bakar, bekas_cakar, luka_lama');

      // Lokasi luka (JSON untuk menyimpan koordinat body map)
      $table->json('lokasi_luka')->nullable()->after('jenis_tanda_fisik');

      // Foto bukti
      $table->string('foto_bukti')->nullable()->after('lokasi_luka')->comment('path ke file foto');

      // Waktu foto diambil (server timestamp)
      $table->timestamp('waktu_foto')->nullable()->after('foto_bukti');

      // Digital signature dari orang tua/pengantar (base64 encoded)
      $table->longText('signature_pengantar')->nullable()->after('waktu_foto');

      // Nama orang tua/pengantar yang menanda tangani
      $table->string('nama_pengantar')->nullable()->after('signature_pengantar');

      // Status verifikasi
      $table->enum('status_verifikasi', ['pending', 'verified', 'rejected'])->default('pending')->after('nama_pengantar');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('absensis', function (Blueprint $table) {
      $table->dropColumn([
        'kondisi_fisik',
        'jenis_tanda_fisik',
        'lokasi_luka',
        'foto_bukti',
        'waktu_foto',
        'signature_pengantar',
        'nama_pengantar',
        'status_verifikasi',
      ]);
    });
  }
};
