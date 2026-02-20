<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anak_didiks', function (Blueprint $table) {
            $table->unsignedBigInteger('guru_vokasi_id')->nullable()->after('guru_fokus_id');
            $table->foreign('guru_vokasi_id')->references('id')->on('karyawans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('anak_didiks', function (Blueprint $table) {
            $table->dropForeign(['guru_vokasi_id']);
            $table->dropColumn('guru_vokasi_id');
        });
    }
};
