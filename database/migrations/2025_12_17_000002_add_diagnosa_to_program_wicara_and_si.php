<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('program_wicara', function (Blueprint $table) {
      if (!Schema::hasColumn('program_wicara', 'diagnosa')) {
        $table->text('diagnosa')->nullable()->after('anak_didik_id');
      }
    });
    Schema::table('program_si', function (Blueprint $table) {
      if (!Schema::hasColumn('program_si', 'diagnosa')) {
        $table->text('diagnosa')->nullable()->after('anak_didik_id');
      }
    });
  }

  public function down(): void
  {
    Schema::table('program_wicara', function (Blueprint $table) {
      if (Schema::hasColumn('program_wicara', 'diagnosa')) {
        $table->dropColumn('diagnosa');
      }
    });
    Schema::table('program_si', function (Blueprint $table) {
      if (Schema::hasColumn('program_si', 'diagnosa')) {
        $table->dropColumn('diagnosa');
      }
    });
  }
};
