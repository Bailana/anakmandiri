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
        Schema::table('rapors', function (Blueprint $table) {
            if (!Schema::hasColumn('rapors', 'therapy_notes')) {
                $table->text('therapy_notes')->nullable()->after('saran_orang_tua');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rapors', function (Blueprint $table) {
            if (Schema::hasColumn('rapors', 'therapy_notes')) {
                $table->dropColumn('therapy_notes');
            }
        });
    }
};
