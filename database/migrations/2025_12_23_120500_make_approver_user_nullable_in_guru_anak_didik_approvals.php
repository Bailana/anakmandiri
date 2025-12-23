<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
  public function up(): void
  {
    // drop foreign key, make column nullable, re-add foreign key
    Schema::table('guru_anak_didik_approvals', function (Blueprint $table) {
      // attempt to drop FK by conventional name
      try {
        $table->dropForeign(['approver_user_id']);
      } catch (\Exception $e) {
        // ignore if not exists
      }
    });

    // modify column to nullable using raw SQL
    DB::statement('ALTER TABLE `guru_anak_didik_approvals` MODIFY `approver_user_id` BIGINT UNSIGNED NULL');

    Schema::table('guru_anak_didik_approvals', function (Blueprint $table) {
      $table->foreign('approver_user_id')->references('id')->on('users')->onDelete('cascade');
    });
  }

  public function down(): void
  {
    Schema::table('guru_anak_didik_approvals', function (Blueprint $table) {
      try {
        $table->dropForeign(['approver_user_id']);
      } catch (\Exception $e) {
      }
    });

    DB::statement('ALTER TABLE `guru_anak_didik_approvals` MODIFY `approver_user_id` BIGINT UNSIGNED NOT NULL');

    Schema::table('guru_anak_didik_approvals', function (Blueprint $table) {
      $table->foreign('approver_user_id')->references('id')->on('users')->onDelete('cascade');
    });
  }
};
