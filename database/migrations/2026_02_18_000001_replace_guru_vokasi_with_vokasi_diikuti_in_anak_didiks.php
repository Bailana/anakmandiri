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
            // Drop foreign/guru_vokasi_id if exists
            if (Schema::hasColumn('anak_didiks', 'guru_vokasi_id')) {
                // Try to drop foreign key if present
                try {
                    $table->dropForeign(['guru_vokasi_id']);
                } catch (\Throwable $e) {
                    // ignore if foreign key doesn't exist
                }
                try {
                    $table->dropColumn('guru_vokasi_id');
                } catch (\Throwable $e) {
                    // ignore
                }
            }

            if (!Schema::hasColumn('anak_didiks', 'vokasi_diikuti')) {
                $table->json('vokasi_diikuti')->nullable()->after('guru_fokus_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anak_didiks', function (Blueprint $table) {
            if (Schema::hasColumn('anak_didiks', 'vokasi_diikuti')) {
                $table->dropColumn('vokasi_diikuti');
            }
            if (!Schema::hasColumn('anak_didiks', 'guru_vokasi_id')) {
                $table->unsignedBigInteger('guru_vokasi_id')->nullable()->after('guru_fokus_id');
                $table->foreign('guru_vokasi_id')->references('id')->on('karyawans')->nullOnDelete();
            }
        });
    }
};
