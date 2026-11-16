<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$fks = [
    ['faculty_availabilities', 'faculty_availabilities_faculty_id_foreign'],
    ['section_rooms', 'section_rooms_room_id_foreign'],
    ['section_rooms', 'section_rooms_section_id_foreign'],
    ['subject_labs', 'subject_labs_room_id_foreign'],
    ['subject_labs', 'subject_labs_subject_id_foreign'],
    ['timetables', 'timetables_classroom_id_foreign'],
    ['timetables', 'timetables_day_id_foreign'],
    ['timetables', 'timetables_faculty_id_foreign'],
    ['timetables', 'timetables_section_id_foreign'],
    ['timetables', 'timetables_subject_id_foreign'],
    ['timetable_locks', 'timetable_locks_day_id_foreign'],
    ['timetable_locks', 'timetable_locks_section_id_foreign']
];

foreach ($fks as $fk) {
    try {
        DB::statement('ALTER TABLE ' . $fk[0] . ' DROP FOREIGN KEY ' . $fk[1]);
        echo "Dropped " . $fk[1] . " from " . $fk[0] . "\n";
    } catch (\Exception $e) {
        echo "Failed " . $fk[1] . ": " . $e->getMessage() . "\n";
    }
}
