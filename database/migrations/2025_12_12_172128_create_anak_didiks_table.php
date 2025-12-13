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
        Schema::create('anak_didiks', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nis')->unique();
            $table->enum('jenis_kelamin', ['laki-laki', 'perempuan']);
            $table->date('tanggal_lahir')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->text('alamat')->nullable();
            $table->string('no_telepon_orang_tua')->nullable();
            $table->string('no_kk')->nullable();
            $table->string('nik')->nullable();
            $table->string('no_akta_kelahiran')->nullable();
            $table->decimal('tinggi_badan', 5, 2)->nullable();
            $table->decimal('berat_badan', 5, 2)->nullable();
            $table->integer('jumlah_saudara_kandung')->nullable();
            $table->integer('anak_ke')->nullable();
            $table->string('tinggal_bersama')->nullable();
            $table->string('pendidikan_terakhir')->nullable();
            $table->string('asal_sekolah')->nullable();
            $table->date('tanggal_pendaftaran')->nullable();
            $table->boolean('kk')->default(false);
            $table->boolean('ktp_orang_tua')->default(false);
            $table->boolean('akta_kelahiran')->default(false);
            $table->boolean('foto_anak')->default(false);
            $table->boolean('pemeriksaan_tes_rambut')->default(false);
            $table->boolean('anamnesa')->default(false);
            $table->boolean('tes_iq')->default(false);
            $table->boolean('pemeriksaan_dokter_lab')->default(false);
            $table->boolean('surat_pernyataan')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anak_didiks');
    }
};
