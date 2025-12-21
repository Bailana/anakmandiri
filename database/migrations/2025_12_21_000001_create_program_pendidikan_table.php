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
        Schema::create('program_pendidikan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('anak_didik_id')->nullable();
            $table->unsignedBigInteger('konsultan_id')->nullable();
            $table->string('kode_program')->nullable();
            $table->string('nama_program')->nullable();
            $table->text('tujuan')->nullable();
            $table->text('aktivitas')->nullable();
            $table->date('periode_mulai')->nullable();
            $table->date('periode_selesai')->nullable();
            $table->text('keterangan')->nullable();
            $table->tinyInteger('is_suggested')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            // optional indexes
            $table->index('anak_didik_id');
            $table->index('konsultan_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('program_pendidikan');
    }
};
