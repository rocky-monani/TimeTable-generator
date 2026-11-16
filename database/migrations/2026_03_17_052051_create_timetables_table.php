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
        Schema::create('timetables', function (Blueprint $table) {
    $table->id();

    $table->unsignedBigInteger('section_id');
    $table->unsignedBigInteger('subject_id');
    $table->unsignedBigInteger('faculty_id');
    $table->unsignedBigInteger('classroom_id');
    $table->unsignedBigInteger('day_id');

    $table->integer('slot_no');
    $table->enum('type', ['lecture', 'lab']);
    $table->string('part')->nullable();

    $table->timestamps();

    // ✅ Foreign keys (MATCH YOUR DB)
    $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
    $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
    $table->foreign('faculty_id')->references('id')->on('faculties')->onDelete('cascade');
    $table->foreign('classroom_id')->references('id')->on('rooms')->onDelete('cascade');
    $table->foreign('day_id')->references('id')->on('week_days')->onDelete('cascade');

    // ✅ Prevent clashes
    $table->unique(['section_id','day_id','slot_no']);
    $table->unique(['faculty_id','day_id','slot_no']);
    $table->unique(['classroom_id','day_id','slot_no']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetables');
    }
};
