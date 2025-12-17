<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up()
  {
    Schema::table('program_psikologi', function (Blueprint $table) {
      if (!Schema::hasColumn('program_psikologi', 'konsultan_id')) {
        $table->unsignedBigInteger('konsultan_id')->nullable()->after('user_id');
        $table->foreign('konsultan_id')->references('id')->on('konsultans')->onDelete('set null');
      }
    });
  }

  public function down()
  {
    Schema::table('program_psikologi', function (Blueprint $table) {
      if (Schema::hasColumn('program_psikologi', 'konsultan_id')) {
        $table->dropForeign(['konsultan_id']);
        $table->dropColumn('konsultan_id');
      }
    });
  }
};
