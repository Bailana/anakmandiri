<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up()
  {
    Schema::table('program_psikologi', function (Blueprint $table) {
      if (!Schema::hasColumn('program_psikologi', 'diagnosa_psikologi')) {
        $table->text('diagnosa_psikologi')->nullable();
      }
    });
  }

  public function down()
  {
    Schema::table('program_psikologi', function (Blueprint $table) {
      if (Schema::hasColumn('program_psikologi', 'diagnosa_psikologi')) {
        $table->dropColumn('diagnosa_psikologi');
      }
    });
  }
};
