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
        Schema::create('rapor_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rapor_id')->constrained('rapors')->onDelete('cascade');
            $table->string('nama_program');
            $table->char('nilai_huruf', 1); // A, B, C, D
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rapor_items');
    }
};
