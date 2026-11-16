<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('timetable_locks', function (Blueprint $table) {

            // 🔥 STEP 1: DROP FOREIGN KEYS
            $table->dropForeign(['subject_id']);
            $table->dropForeign(['faculty_id']);
            $table->dropForeign(['classroom_id']);

            // 🔥 STEP 2: DROP COLUMNS
            $table->dropColumn(['subject_id', 'faculty_id', 'classroom_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timetable_locks', function (Blueprint $table) {
            //
        });
    }
};
