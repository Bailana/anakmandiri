<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::table('program_wicara', function (Blueprint $table) {
      $table->unsignedBigInteger('konsultan_id')->nullable()->after('anak_didik_id');
      $table->foreign('konsultan_id')->references('id')->on('konsultans')->onDelete('set null');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('program_wicara', function (Blueprint $table) {
      $table->dropForeign(['konsultan_id']);
      $table->dropColumn('konsultan_id');
    });
  }
};
