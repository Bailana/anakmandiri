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
    Schema::table('guru_anak_didik_schedules', function (Blueprint $table) {
      if (!Schema::hasColumn('guru_anak_didik_schedules', 'terapis_nama')) {
        $table->string('terapis_nama')->nullable()->after('jenis_terapi');
      }
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('guru_anak_didik_schedules', function (Blueprint $table) {
      if (Schema::hasColumn('guru_anak_didik_schedules', 'terapis_nama')) {
        $table->dropColumn('terapis_nama');
      }
    });
  }
};
