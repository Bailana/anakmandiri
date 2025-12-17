<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up()
  {
    Schema::table('program_psikologi', function (Blueprint $table) {
      if (Schema::hasColumn('program_psikologi', 'kemampuan')) {
        $table->dropColumn('kemampuan');
      }
      if (Schema::hasColumn('program_psikologi', 'wawancara')) {
        $table->dropColumn('wawancara');
      }
      if (Schema::hasColumn('program_psikologi', 'diagnosa')) {
        $table->dropColumn('diagnosa');
      }
      if (Schema::hasColumn('program_psikologi', 'kemampuan_saat_ini')) {
        $table->dropColumn('kemampuan_saat_ini');
      }
      if (Schema::hasColumn('program_psikologi', 'saran_rekomendasi')) {
        $table->dropColumn('saran_rekomendasi');
      }
    });
  }

  public function down()
  {
    Schema::table('program_psikologi', function (Blueprint $table) {
      if (!Schema::hasColumn('program_psikologi', 'kemampuan')) {
        $table->json('kemampuan')->nullable();
      }
      if (!Schema::hasColumn('program_psikologi', 'wawancara')) {
        $table->text('wawancara')->nullable();
      }
      if (!Schema::hasColumn('program_psikologi', 'diagnosa')) {
        $table->text('diagnosa')->nullable();
      }
      if (!Schema::hasColumn('program_psikologi', 'kemampuan_saat_ini')) {
        $table->text('kemampuan_saat_ini')->nullable();
      }
      if (!Schema::hasColumn('program_psikologi', 'saran_rekomendasi')) {
        $table->text('saran_rekomendasi')->nullable();
      }
    });
  }
};
