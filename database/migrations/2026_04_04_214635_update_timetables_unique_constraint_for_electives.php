<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Drop the old (section_id, day_id, slot_no) unique constraint and replace it
     * with (section_id, day_id, slot_no, subject_id) so that multiple elective
     * subjects can be stored for the same section at the same time slot.
     */
    public function up(): void
    {
        // Drop old unique constraint using raw SQL (safer across MySQL versions)
        DB::statement('ALTER TABLE timetables DROP INDEX timetables_section_id_day_id_slot_no_unique');

        // Add new constraint: same subject cannot appear twice for same section+day+slot
        DB::statement('ALTER TABLE timetables ADD UNIQUE INDEX timetables_section_day_slot_subject_unique (section_id, day_id, slot_no, subject_id)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE timetables DROP INDEX timetables_section_day_slot_subject_unique');
        DB::statement('ALTER TABLE timetables ADD UNIQUE INDEX timetables_section_id_day_id_slot_no_unique (section_id, day_id, slot_no)');
    }
};
