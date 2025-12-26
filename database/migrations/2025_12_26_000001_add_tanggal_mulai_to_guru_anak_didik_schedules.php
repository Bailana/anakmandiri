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
      if (!Schema::hasColumn('guru_anak_didik_schedules', 'tanggal_mulai')) {
        $table->date('tanggal_mulai')->nullable()->after('hari');
      }
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('guru_anak_didik_schedules', function (Blueprint $table) {
      if (Schema::hasColumn('guru_anak_didik_schedules', 'tanggal_mulai')) {
        $table->dropColumn('tanggal_mulai');
      }
    });
  }
};
