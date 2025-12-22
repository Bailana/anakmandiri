<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
  {
    Schema::table('program_anak', function (Blueprint $table) {
      if (!Schema::hasColumn('program_anak', 'created_by')) {
        $table->unsignedBigInteger('created_by')->nullable()->after('is_suggested');
      }
      if (!Schema::hasColumn('program_anak', 'created_by_name')) {
        $table->string('created_by_name')->nullable()->after('created_by');
      }
    });
  }

  public function down()
  {
    Schema::table('program_anak', function (Blueprint $table) {
      if (Schema::hasColumn('program_anak', 'created_by_name')) {
        $table->dropColumn('created_by_name');
      }
      if (Schema::hasColumn('program_anak', 'created_by')) {
        $table->dropColumn('created_by');
      }
    });
  }
};
