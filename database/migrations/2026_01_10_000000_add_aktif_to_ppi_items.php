<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
  {
    if (!Schema::hasColumn('ppi_items', 'aktif')) {
      Schema::table('ppi_items', function (Blueprint $table) {
        $table->boolean('aktif')->default(false)->after('kategori');
      });
    }
  }

  public function down()
  {
    if (Schema::hasColumn('ppi_items', 'aktif')) {
      Schema::table('ppi_items', function (Blueprint $table) {
        $table->dropColumn('aktif');
      });
    }
  }
};
