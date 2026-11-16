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
        Schema::table('timetables', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['faculty_id']);
            $table->dropForeign(['classroom_id']);

            // Drop the unique indexes
            $table->dropUnique(['faculty_id', 'day_id', 'slot_no']);
            $table->dropUnique(['classroom_id', 'day_id', 'slot_no']);

            // Re-add foreign keys (MySQL will create non-unique indexes)
            $table->foreign('faculty_id')->references('id')->on('faculties')->onDelete('cascade');
            $table->foreign('classroom_id')->references('id')->on('rooms')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            $table->unique(['faculty_id', 'day_id', 'slot_no']);
            $table->unique(['classroom_id', 'day_id', 'slot_no']);
        });
    }
};
