<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacultyAvailabilitiesTable extends Migration
{
    public function up()
    {
        Schema::create('faculty_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained()->onDelete('cascade');
            $table->integer('day_id'); // 1 = Mon ... 6 = Sat
            $table->integer('slot_no');
            $table->boolean('is_available')->default(1);
            $table->timestamps();

            // 🔥 IMPORTANT (prevents duplicates + improves performance)
            $table->unique(['faculty_id', 'day_id', 'slot_no']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('faculty_availabilities');
    }
}