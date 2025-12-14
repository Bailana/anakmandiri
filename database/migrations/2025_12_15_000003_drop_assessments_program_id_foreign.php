<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('assessments', function ($table) {
      $table->dropForeign(['program_id']);
    });
  }
  public function down(): void
  {
    // You may want to re-add the foreign key here if needed
    // $table->foreign('program_id')->references('id')->on('programs')->onDelete('cascade');
  }
};
