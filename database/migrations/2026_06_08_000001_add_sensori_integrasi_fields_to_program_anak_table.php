<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('program_anak', function (Blueprint $table) {
      if (!Schema::hasColumn('program_anak', 'target_area')) {
        $table->text('target_area')->nullable()->after('nama_program');
      }
      if (!Schema::hasColumn('program_anak', 'aktivitas_sensori')) {
        $table->text('aktivitas_sensori')->nullable()->after('aktivitas');
      }
      if (!Schema::hasColumn('program_anak', 'durasi')) {
        $table->unsignedInteger('durasi')->nullable()->after('aktivitas_sensori');
      }
    });
  }

  public function down(): void
  {
    Schema::table('program_anak', function (Blueprint $table) {
      if (Schema::hasColumn('program_anak', 'durasi')) {
        $table->dropColumn('durasi');
      }
      if (Schema::hasColumn('program_anak', 'aktivitas_sensori')) {
        $table->dropColumn('aktivitas_sensori');
      }
      if (Schema::hasColumn('program_anak', 'target_area')) {
        $table->dropColumn('target_area');
      }
    });
  }
};
