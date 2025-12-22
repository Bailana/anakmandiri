<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
  {
    Schema::create('program_anak_audits', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('program_anak_id')->nullable();
      $table->string('action'); // create, update, delete
      $table->unsignedBigInteger('user_id')->nullable();
      $table->string('user_name')->nullable();
      $table->json('changes')->nullable();
      $table->timestamps();

      $table->index('program_anak_id');
      $table->index('user_id');
    });
  }

  public function down()
  {
    Schema::dropIfExists('program_anak_audits');
  }
};
