<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKeteranganToProgramKonsultanTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('program_konsultan', function (Blueprint $table) {
      if (!Schema::hasColumn('program_konsultan', 'keterangan')) {
        $table->text('keterangan')->nullable()->after('aktivitas');
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
    Schema::table('program_konsultan', function (Blueprint $table) {
      if (Schema::hasColumn('program_konsultan', 'keterangan')) {
        $table->dropColumn('keterangan');
      }
    });
  }
}
