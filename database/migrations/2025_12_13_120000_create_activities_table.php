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
    Schema::create('activities', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
      $table->string('action'); // create, update, delete, login, logout, approve, reject, etc
      $table->string('description'); // Deskripsi aktivitas
      $table->string('model_name')->nullable(); // Nama model yang diakses (AnakDidik, Konsultan, dll)
      $table->unsignedBigInteger('model_id')->nullable(); // ID dari model yang diakses
      $table->ipAddress('ip_address')->nullable();
      $table->text('user_agent')->nullable();
      $table->timestamps();

      // Indexes untuk query performa
      $table->index('user_id');
      $table->index('created_at');
      $table->index('action');
      $table->index('model_name');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('activities');
  }
};
