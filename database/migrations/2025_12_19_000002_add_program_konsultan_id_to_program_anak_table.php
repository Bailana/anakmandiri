<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
  {
    Schema::table('program_anak', function (Blueprint $table) {
      $table->unsignedBigInteger('program_konsultan_id')->nullable()->after('id');
      $table->foreign('program_konsultan_id')->references('id')->on('program_konsultan')->onDelete('set null');
    });
  }

  public function down()
  {
    Schema::table('program_anak', function (Blueprint $table) {
      $table->dropForeign(['program_konsultan_id']);
      $table->dropColumn('program_konsultan_id');
    });
  }
};
