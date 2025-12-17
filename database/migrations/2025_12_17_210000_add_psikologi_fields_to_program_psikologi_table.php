<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up()
  {
    Schema::table('program_psikologi', function (Blueprint $table) {
      if (!Schema::hasColumn('program_psikologi', 'latar_belakang')) {
        $table->text('latar_belakang')->nullable();
      }
      if (!Schema::hasColumn('program_psikologi', 'metode_assessment')) {
        $table->text('metode_assessment')->nullable();
      }
      if (!Schema::hasColumn('program_psikologi', 'hasil_assessment')) {
        $table->text('hasil_assessment')->nullable();
      }
      if (!Schema::hasColumn('program_psikologi', 'kesimpulan')) {
        $table->text('kesimpulan')->nullable();
      }
      if (!Schema::hasColumn('program_psikologi', 'rekomendasi')) {
        $table->text('rekomendasi')->nullable();
      }
    });
  }

  public function down()
  {
    Schema::table('program_psikologi', function (Blueprint $table) {
      if (Schema::hasColumn('program_psikologi', 'latar_belakang')) {
        $table->dropColumn('latar_belakang');
      }
      if (Schema::hasColumn('program_psikologi', 'metode_assessment')) {
        $table->dropColumn('metode_assessment');
      }
      if (Schema::hasColumn('program_psikologi', 'hasil_assessment')) {
        $table->dropColumn('hasil_assessment');
      }
      if (Schema::hasColumn('program_psikologi', 'kesimpulan')) {
        $table->dropColumn('kesimpulan');
      }
      if (Schema::hasColumn('program_psikologi', 'rekomendasi')) {
        $table->dropColumn('rekomendasi');
      }
    });
  }
};
