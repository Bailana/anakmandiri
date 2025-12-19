<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
  {
    Schema::table('program_anak', function (Blueprint $table) {
      $table->text('tujuan')->nullable()->after('nama_program');
      $table->text('aktivitas')->nullable()->after('tujuan');
    });
  }

  public function down()
  {
    Schema::table('program_anak', function (Blueprint $table) {
      $table->dropColumn(['tujuan', 'aktivitas']);
    });
  }
};
