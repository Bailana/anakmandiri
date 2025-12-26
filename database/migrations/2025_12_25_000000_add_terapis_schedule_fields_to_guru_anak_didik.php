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
    Schema::table('guru_anak_didik', function (Blueprint $table) {
      $table->time('jam_mulai')->nullable()->after('tanggal_mulai');
      $table->string('jenis_terapi')->nullable()->after('jam_mulai');
      $table->string('terapis_nama')->nullable()->after('jenis_terapi');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('guru_anak_didik', function (Blueprint $table) {
      $table->dropColumn(['jam_mulai', 'jenis_terapi', 'terapis_nama']);
    });
  }
};
