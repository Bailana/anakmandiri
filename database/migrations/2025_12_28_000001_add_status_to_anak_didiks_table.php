<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('anak_didiks', function (Blueprint $table) {
      $table->enum('status', ['aktif', 'nonaktif', 'keluar'])->default('aktif')->after('tanggal_pendaftaran');
    });
  }

  public function down(): void
  {
    Schema::table('anak_didiks', function (Blueprint $table) {
      $table->dropColumn('status');
    });
  }
};
