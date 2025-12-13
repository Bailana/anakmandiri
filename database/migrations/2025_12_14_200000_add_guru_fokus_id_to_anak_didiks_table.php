<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('anak_didiks', function (Blueprint $table) {
      $table->unsignedBigInteger('guru_fokus_id')->nullable()->after('id');
      $table->foreign('guru_fokus_id')->references('id')->on('karyawans')->nullOnDelete();
    });
  }

  public function down(): void
  {
    Schema::table('anak_didiks', function (Blueprint $table) {
      $table->dropForeign(['guru_fokus_id']);
      $table->dropColumn('guru_fokus_id');
    });
  }
};
