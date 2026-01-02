<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    if (!Schema::hasTable('karyawans')) return;

    Schema::table('karyawans', function (Blueprint $table) {
      if (!Schema::hasColumn('karyawans', 'user_id')) {
        $table->unsignedBigInteger('user_id')->nullable()->after('id');
        $table->index('user_id');
        $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
      }
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    if (!Schema::hasTable('karyawans')) return;

    Schema::table('karyawans', function (Blueprint $table) {
      if (Schema::hasColumn('karyawans', 'user_id')) {
        // Drop foreign key if exists
        try {
          $table->dropForeign(['user_id']);
        } catch (\Throwable $e) {
          // ignore
        }
        try {
          $table->dropIndex(['user_id']);
        } catch (\Throwable $e) {
          // ignore
        }
        $table->dropColumn('user_id');
      }
    });
  }
};
