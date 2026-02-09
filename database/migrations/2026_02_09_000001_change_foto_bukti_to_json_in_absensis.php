<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    // First, update all existing data to NULL (clear existing invalid data)
    DB::statement('UPDATE `absensis` SET `foto_bukti` = NULL WHERE `foto_bukti` IS NOT NULL AND `foto_bukti` != ""');

    // Then change column type to JSON
    Schema::table('absensis', function (Blueprint $table) {
      DB::statement('ALTER TABLE `absensis` MODIFY `foto_bukti` JSON NULL');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('absensis', function (Blueprint $table) {
      $table->string('foto_bukti')->nullable()->change();
    });
  }
};
