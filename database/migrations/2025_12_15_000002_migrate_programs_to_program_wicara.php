<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    // Copy data from programs to program_wicara
    DB::statement('INSERT INTO program_wicara (anak_didik_id, kemampuan, wawancara, kemampuan_saat_ini, saran_rekomendasi, created_at, updated_at)
            SELECT anak_didik_id, kemampuan, wawancara, kemampuan_saat_ini, saran_rekomendasi, created_at, updated_at FROM programs');
    // Drop old table
    Schema::dropIfExists('programs');
  }
  public function down(): void
  {
    // No rollback for data migration
  }
};
