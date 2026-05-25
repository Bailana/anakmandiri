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
    Schema::table('rapors', function (Blueprint $table) {
      if (!Schema::hasColumn('rapors', 'saran_guru')) {
        $table->text('saran_guru')->nullable()->after('group_notes');
      }
      if (!Schema::hasColumn('rapors', 'saran_orang_tua')) {
        $table->text('saran_orang_tua')->nullable()->after('saran_guru');
      }
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('rapors', function (Blueprint $table) {
      if (Schema::hasColumn('rapors', 'saran_orang_tua')) {
        $table->dropColumn('saran_orang_tua');
      }
      if (Schema::hasColumn('rapors', 'saran_guru')) {
        $table->dropColumn('saran_guru');
      }
    });
  }
};
