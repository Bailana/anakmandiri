<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdToAssessments extends Migration
{
  public function up()
  {
    Schema::table('assessments', function (Blueprint $table) {
      if (!Schema::hasColumn('assessments', 'user_id')) {
        $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->after('konsultan_id');
      }
    });
  }

  public function down()
  {
    Schema::table('assessments', function (Blueprint $table) {
      if (Schema::hasColumn('assessments', 'user_id')) {
        $table->dropForeign(['user_id']);
        $table->dropColumn('user_id');
      }
    });
  }
}
