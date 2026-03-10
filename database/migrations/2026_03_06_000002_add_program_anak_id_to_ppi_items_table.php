<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('ppi_items', function (Blueprint $table) {
      if (!Schema::hasColumn('ppi_items', 'program_anak_id')) {
        $table->unsignedBigInteger('program_anak_id')->nullable()->after('ppi_id');
        $table->foreign('program_anak_id')->references('id')->on('program_anak')->onDelete('set null');
      }
    });
  }

  public function down(): void
  {
    Schema::table('ppi_items', function (Blueprint $table) {
      if (Schema::hasColumn('ppi_items', 'program_anak_id')) {
        $table->dropForeign(['program_anak_id']);
        $table->dropColumn('program_anak_id');
      }
    });
  }
};
