<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('guru_anak_didik_schedules', function (Blueprint $table) {
      if (!Schema::hasColumn('guru_anak_didik_schedules', 'jenis_terapi')) {
        $table->string('jenis_terapi')->nullable()->after('jam_mulai');
      }
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('guru_anak_didik_schedules', function (Blueprint $table) {
      if (Schema::hasColumn('guru_anak_didik_schedules', 'jenis_terapi')) {
        $table->dropColumn('jenis_terapi');
      }
    });
  }
};
