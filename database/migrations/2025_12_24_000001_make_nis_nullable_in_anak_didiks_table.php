<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    // Note: This uses the `change()` column modifier which requires the doctrine/dbal package.
    Schema::table('anak_didiks', function (Blueprint $table) {
      $table->string('nis')->nullable()->change();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('anak_didiks', function (Blueprint $table) {
      $table->string('nis')->nullable(false)->change();
    });
  }
};
