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
    Schema::create('therapy_programs', function (Blueprint $table) {
      $table->id();
      $table->foreignId('anak_didik_id')->constrained('anak_didiks')->onDelete('cascade');
      $table->enum('type_therapy', ['si', 'wicara', 'perilaku'])->comment('SI=Sensori Integrasi, Wicara=Terapis Wicara, Perilaku=Terapis Perilaku');
      $table->date('tanggal_mulai')->nullable();
      $table->date('tanggal_selesai')->nullable();
      $table->text('notes')->nullable();
      $table->boolean('is_active')->default(true);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('therapy_programs');
  }
};
