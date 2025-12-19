<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
  {
    Schema::table('program_anak', function (Blueprint $table) {
      $table->string('kode_program')->nullable()->after('program_konsultan_id');
    });
  }

  public function down()
  {
    Schema::table('program_anak', function (Blueprint $table) {
      $table->dropColumn('kode_program');
    });
  }
};
