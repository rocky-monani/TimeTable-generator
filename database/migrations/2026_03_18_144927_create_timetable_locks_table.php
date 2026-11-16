<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_locks', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('faculty_id');
            $table->unsignedBigInteger('classroom_id')->nullable();

            $table->unsignedBigInteger('day_id');
            $table->integer('slot_no');

            $table->enum('type', ['lecture', 'lab'])->default('lecture');

            $table->timestamps();

            // 🔗 Foreign Keys (optional but recommended)
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->foreign('faculty_id')->references('id')->on('faculties')->onDelete('cascade');
            $table->foreign('classroom_id')->references('id')->on('rooms')->onDelete('set null');
            $table->foreign('day_id')->references('id')->on('week_days')->onDelete('cascade');

            // 🚀 Prevent duplicate locks on same slot
            $table->unique(['section_id', 'day_id', 'slot_no'], 'unique_lock_per_slot');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_locks');
    }
};