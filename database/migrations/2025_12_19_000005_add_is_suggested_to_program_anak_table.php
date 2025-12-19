<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
  {
    Schema::table('program_anak', function (Blueprint $table) {
      $table->boolean('is_suggested')->default(false)->after('kode_program');
    });
  }

  public function down()
  {
    Schema::table('program_anak', function (Blueprint $table) {
      $table->dropColumn('is_suggested');
    });
  }
};
