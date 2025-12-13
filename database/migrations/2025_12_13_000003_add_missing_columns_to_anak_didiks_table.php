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
    Schema::table('anak_didiks', function (Blueprint $table) {
      $table->string('no_telepon')->nullable()->after('alamat');
      $table->string('email')->nullable()->after('no_telepon');
      $table->string('nama_orang_tua')->nullable()->after('email');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('anak_didiks', function (Blueprint $table) {
      $table->dropColumn(['no_telepon', 'email', 'nama_orang_tua']);
    });
  }
};
