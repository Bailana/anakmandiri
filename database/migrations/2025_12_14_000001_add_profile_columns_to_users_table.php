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
    Schema::table('users', function (Blueprint $table) {
      // Tambahkan kolom profil jika belum ada
      if (!Schema::hasColumn('users', 'phone')) {
        $table->string('phone')->nullable()->after('email');
      }
      if (!Schema::hasColumn('users', 'address')) {
        $table->string('address')->nullable()->after('phone');
      }
      if (!Schema::hasColumn('users', 'city')) {
        $table->string('city')->nullable()->after('address');
      }
      if (!Schema::hasColumn('users', 'state')) {
        $table->string('state')->nullable()->after('city');
      }
      if (!Schema::hasColumn('users', 'zip_code')) {
        $table->string('zip_code')->nullable()->after('state');
      }
      if (!Schema::hasColumn('users', 'country')) {
        $table->string('country')->nullable()->after('zip_code');
      }
      if (!Schema::hasColumn('users', 'bio')) {
        $table->text('bio')->nullable()->after('country');
      }
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('users', function (Blueprint $table) {
      $table->dropColumnIfExists('phone');
      $table->dropColumnIfExists('address');
      $table->dropColumnIfExists('city');
      $table->dropColumnIfExists('state');
      $table->dropColumnIfExists('zip_code');
      $table->dropColumnIfExists('country');
      $table->dropColumnIfExists('bio');
    });
  }
};
