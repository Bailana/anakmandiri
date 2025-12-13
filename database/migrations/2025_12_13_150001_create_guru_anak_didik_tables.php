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
    Schema::create('guru_anak_didik', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
      $table->foreignId('anak_didik_id')->constrained('anak_didiks')->onDelete('cascade');
      $table->enum('status', ['aktif', 'non-aktif'])->default('aktif');
      $table->timestamp('tanggal_mulai')->nullable();
      $table->timestamp('tanggal_selesai')->nullable();
      $table->text('catatan')->nullable();
      $table->timestamps();

      // Unique constraint - satu guru hanya bisa mengampu max 3 anak
      $table->unique(['user_id', 'anak_didik_id']);
    });

    Schema::create('guru_anak_didik_approvals', function (Blueprint $table) {
      $table->id();
      $table->foreignId('requester_user_id')->constrained('users')->onDelete('cascade'); // guru yang request
      $table->foreignId('approver_user_id')->constrained('users')->onDelete('cascade'); // guru fokus yang approve
      $table->foreignId('anak_didik_id')->constrained('anak_didiks')->onDelete('cascade');
      $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
      $table->text('reason')->nullable();
      $table->text('approval_notes')->nullable();
      $table->timestamp('approved_at')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('guru_anak_didik_approvals');
    Schema::dropIfExists('guru_anak_didik');
  }
};
