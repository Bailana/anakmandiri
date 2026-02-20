<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJenisVokasiToProgramAnakTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('program_anak', function (Blueprint $table) {
            // store selected vokasi types as JSON array
            if (!Schema::hasColumn('program_anak', 'jenis_vokasi')) {
                $table->json('jenis_vokasi')->nullable()->after('keterangan');
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
        Schema::table('program_anak', function (Blueprint $table) {
            if (Schema::hasColumn('program_anak', 'jenis_vokasi')) {
                $table->dropColumn('jenis_vokasi');
            }
        });
    }
}
