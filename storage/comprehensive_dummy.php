<?php

use Illuminate\Support\Facades\DB;

// ============================================================
// CLEAN SLATE (preserve time_slots & week_days)
// ============================================================
DB::statement('SET FOREIGN_KEY_CHECKS=0;');
DB::table('timetables')->truncate();
DB::table('subject_labs')->truncate();
DB::table('section_rooms')->truncate();
DB::table('subjects')->truncate();
DB::table('sections')->truncate();
DB::table('departments')->truncate();
DB::table('faculties')->truncate();
DB::table('rooms')->truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');

// ============================================================
// DEPARTMENTS
// ============================================================
$deptCS = DB::table('departments')->insertGetId(['name' => 'Computer Science',       'created_at' => now(), 'updated_at' => now()]);
$deptIT = DB::table('departments')->insertGetId(['name' => 'Information Technology', 'created_at' => now(), 'updated_at' => now()]);

// ============================================================
// SUBJECT NAMES (unique per department)
// ============================================================
$csSubjectNames = [
    'Data Structures & Algorithms',
    'Computer Organization',
    'Compiler Design',
    'Computer Graphics',
    'Artificial Intelligence',
    'Cryptography & Network Security',
];
$itSubjectNames = [
    'Database Management Systems',
    'Web Development',
    'Cloud Computing',
    'Mobile Application Development',
    'Software Testing & QA',
    'Network Administration',
];

// ============================================================
// FACULTY – 30 total, ALL maxDailyHours = 4
//
// Faculty load analysis with maxDailyHours=4:
//   Theory faculty: 2 sections × 3 hrs/week = 6 slots → across 6 days = 1 slot/day avg ✓
//   Lab faculty:    2 sections × 2 batches   = 4 lab-slot-pairs
//                   Each pair = 2 consecutive slots = counts as 2 against daily limit
//                   So lab faculty can do 2 session-pairs/day (4 slots used)
//                   4 pairs needed, spread over 6 days → fine ✓
//
// KEY: Creative Break and L&D use their OWN standalone faculty,
//      so they NEVER consume any core faculty's 4-hr/day quota.
// ============================================================

$csTheoryNames = ['Ajay Sharma',     'Bindu Nair',      'Chetan Verma',
                  'Deepika Menon',   'Ekta Joshi',      'Farhan Qureshi'];
$itTheoryNames = ['Girish Rao',      'Hina Kapoor',     'Imran Sheikh',
                  'Jyoti Desai',     'Kartik Malhotra', 'Lata Iyer'];
$csLabNames    = ['Manav Pillai',    'Neeta Gupta',     'Om Prakash',
                  'Poonam Tiwari',   'Qasim Ansari',    'Rekha Shetty'];
$itLabNames    = ['Suresh Naidu',    'Tina Bose',       'Uday Kulkarni',
                  'Varsha Pandey',   'Wasim Alvi',      "Xenia D'Souza"];

$tf  = [];
$itf = [];
$lf  = [];
$ilf = [];

for ($i = 0; $i < 6; $i++) {
    $tf[$i]  = DB::table('faculties')->insertGetId(['name' => $csTheoryNames[$i] . ' (Theory-CS)', 'weeklyLoad' => 30, 'maxDailyHours' => 4, 'created_at' => now(), 'updated_at' => now()]);
    $itf[$i] = DB::table('faculties')->insertGetId(['name' => $itTheoryNames[$i] . ' (Theory-IT)', 'weeklyLoad' => 30, 'maxDailyHours' => 4, 'created_at' => now(), 'updated_at' => now()]);
    $lf[$i]  = DB::table('faculties')->insertGetId(['name' => $csLabNames[$i]   . ' (Lab-CS)',    'weeklyLoad' => 30, 'maxDailyHours' => 4, 'created_at' => now(), 'updated_at' => now()]);
    $ilf[$i] = DB::table('faculties')->insertGetId(['name' => $itLabNames[$i]   . ' (Lab-IT)',    'weeklyLoad' => 30, 'maxDailyHours' => 4, 'created_at' => now(), 'updated_at' => now()]);
}

// Standalone L&D faculty (2) — not shared with any core subject
$ldFacCS = DB::table('faculties')->insertGetId(['name' => 'Yash Pillai (L&D-CS)', 'weeklyLoad' => 30, 'maxDailyHours' => 4, 'created_at' => now(), 'updated_at' => now()]);
$ldFacIT = DB::table('faculties')->insertGetId(['name' => 'Zara Khan (L&D-IT)',   'weeklyLoad' => 30, 'maxDailyHours' => 4, 'created_at' => now(), 'updated_at' => now()]);

// Standalone Creative Break faculty (2) — not shared with any core subject
$cbFacCS = DB::table('faculties')->insertGetId(['name' => 'Anita Rao (CB-CS)',    'weeklyLoad' => 30, 'maxDailyHours' => 4, 'created_at' => now(), 'updated_at' => now()]);
$cbFacIT = DB::table('faculties')->insertGetId(['name' => 'Bimal Shah (CB-IT)',   'weeklyLoad' => 30, 'maxDailyHours' => 4, 'created_at' => now(), 'updated_at' => now()]);

// ============================================================
// ROOMS : 4 classrooms + 6 CS labs + 6 IT labs = 16 rooms
// ============================================================
$classrooms = [];
for ($i = 1; $i <= 4; $i++) {
    $classrooms[$i-1] = DB::table('rooms')->insertGetId(['name' => 'Classroom ' . $i, 'type' => 'classroom', 'created_at' => now(), 'updated_at' => now()]);
}

$csLabs = [];
for ($i = 0; $i < 6; $i++) {
    $csLabs[$i] = DB::table('rooms')->insertGetId(['name' => 'CS Lab ' . ($i + 1) . ' - ' . $csSubjectNames[$i], 'type' => 'lab', 'created_at' => now(), 'updated_at' => now()]);
}

$itLabs = [];
for ($i = 0; $i < 6; $i++) {
    $itLabs[$i] = DB::table('rooms')->insertGetId(['name' => 'IT Lab ' . ($i + 1) . ' - ' . $itSubjectNames[$i], 'type' => 'lab', 'created_at' => now(), 'updated_at' => now()]);
}

// ============================================================
// SECTIONS : CS-A, CS-B, IT-A, IT-B — 2 batches each
// ============================================================
$secCSA = DB::table('sections')->insertGetId(['name' => 'CS-A', 'semester' => 4, 'departmentId' => $deptCS, 'batches' => 2, 'created_at' => now(), 'updated_at' => now()]);
$secCSB = DB::table('sections')->insertGetId(['name' => 'CS-B', 'semester' => 4, 'departmentId' => $deptCS, 'batches' => 2, 'created_at' => now(), 'updated_at' => now()]);
$secITA = DB::table('sections')->insertGetId(['name' => 'IT-A', 'semester' => 4, 'departmentId' => $deptIT, 'batches' => 2, 'created_at' => now(), 'updated_at' => now()]);
$secITB = DB::table('sections')->insertGetId(['name' => 'IT-B', 'semester' => 4, 'departmentId' => $deptIT, 'batches' => 2, 'created_at' => now(), 'updated_at' => now()]);

DB::table('section_rooms')->insert([
    ['section_id' => $secCSA, 'room_id' => $classrooms[0], 'created_at' => now(), 'updated_at' => now()],
    ['section_id' => $secCSB, 'room_id' => $classrooms[1], 'created_at' => now(), 'updated_at' => now()],
    ['section_id' => $secITA, 'room_id' => $classrooms[2], 'created_at' => now(), 'updated_at' => now()],
    ['section_id' => $secITB, 'room_id' => $classrooms[3], 'created_at' => now(), 'updated_at' => now()],
]);

// ============================================================
// SUBJECTS (16 total)
// ============================================================
$csSubjects = [];
$itSubjects = [];

for ($i = 0; $i < 6; $i++) {
    $csSubjects[$i] = DB::table('subjects')->insertGetId([
        'name'                  => $csSubjectNames[$i],
        'subjectCode'           => 'CS4' . sprintf('%02d', $i + 1),
        'semester'              => 4,
        'departmentId'          => $deptCS,
        'theoryHoursPerWeek'    => 3,
        'practicalHoursPerWeek' => 2,
        'faculty_id'            => $tf[$i],
        'faculty_id_2'          => $lf[$i],
        'category'              => 'Core',
        'created_at'            => now(),
        'updated_at'            => now(),
    ]);
    $itSubjects[$i] = DB::table('subjects')->insertGetId([
        'name'                  => $itSubjectNames[$i],
        'subjectCode'           => 'IT4' . sprintf('%02d', $i + 1),
        'semester'              => 4,
        'departmentId'          => $deptIT,
        'theoryHoursPerWeek'    => 3,
        'practicalHoursPerWeek' => 2,
        'faculty_id'            => $itf[$i],
        'faculty_id_2'          => $ilf[$i],
        'category'              => 'Core',
        'created_at'            => now(),
        'updated_at'            => now(),
    ]);
}

// L&D – forced to Day-1 Slot-2 and Day-5 Slot-2 by the generator
$ldCS = DB::table('subjects')->insertGetId([
    'name' => 'L&D - Leadership & Development', 'subjectCode' => 'CS4LD', 'semester' => 4,
    'departmentId' => $deptCS, 'theoryHoursPerWeek' => 2, 'practicalHoursPerWeek' => 0,
    'faculty_id' => $ldFacCS, 'category' => 'Core', 'created_at' => now(), 'updated_at' => now(),
]);
$ldIT = DB::table('subjects')->insertGetId([
    'name' => 'L&D - Leadership & Development', 'subjectCode' => 'IT4LD', 'semester' => 4,
    'departmentId' => $deptIT, 'theoryHoursPerWeek' => 2, 'practicalHoursPerWeek' => 0,
    'faculty_id' => $ldFacIT, 'category' => 'Core', 'created_at' => now(), 'updated_at' => now(),
]);

// Creative Break – forced to Day-4 Slots-4/5 by the generator
$cbCS = DB::table('subjects')->insertGetId([
    'name' => 'Creative Break - CS', 'subjectCode' => 'CS4CB', 'semester' => 4,
    'departmentId' => $deptCS, 'theoryHoursPerWeek' => 0, 'practicalHoursPerWeek' => 2,
    'faculty_id' => $cbFacCS, 'category' => 'Elective', 'created_at' => now(), 'updated_at' => now(),
]);
$cbIT = DB::table('subjects')->insertGetId([
    'name' => 'Creative Break - IT', 'subjectCode' => 'IT4CB', 'semester' => 4,
    'departmentId' => $deptIT, 'theoryHoursPerWeek' => 0, 'practicalHoursPerWeek' => 2,
    'faculty_id' => $cbFacIT, 'category' => 'Elective', 'created_at' => now(), 'updated_at' => now(),
]);

// ============================================================
// SUBJECT-LAB MAPPINGS (CS → csLabs, IT → itLabs, no cross-dept sharing)
// ============================================================
for ($i = 0; $i < 6; $i++) {
    DB::table('subject_labs')->insert(['subject_id' => $csSubjects[$i], 'room_id' => $csLabs[$i], 'created_at' => now(), 'updated_at' => now()]);
    DB::table('subject_labs')->insert(['subject_id' => $itSubjects[$i], 'room_id' => $itLabs[$i], 'created_at' => now(), 'updated_at' => now()]);
}
// Creative Break rooms
DB::table('subject_labs')->insert(['subject_id' => $cbCS, 'room_id' => $csLabs[4], 'created_at' => now(), 'updated_at' => now()]);
DB::table('subject_labs')->insert(['subject_id' => $cbIT, 'room_id' => $itLabs[4], 'created_at' => now(), 'updated_at' => now()]);

// ============================================================
// SUMMARY
// ============================================================
echo "\n✅ Comprehensive dummy data inserted!\n\n";
echo "📦 Departments  : 2 (CS, IT)\n";
echo "🏫 Sections     : 4 (CS-A, CS-B, IT-A, IT-B) — 2 batches each\n";
echo "👨‍🏫 Faculty      : 30 total — maxDailyHours = 4 for ALL\n";
echo "   •  6  CS Theory  : Ajay Sharma … Farhan Qureshi\n";
echo "   •  6  IT Theory  : Girish Rao … Lata Iyer\n";
echo "   •  6  CS Lab     : Manav Pillai … Rekha Shetty\n";
echo "   •  6  IT Lab     : Suresh Naidu … Xenia D'Souza\n";
echo "   •  1  L&D-CS     : Yash Pillai\n";
echo "   •  1  L&D-IT     : Zara Khan\n";
echo "   •  1  CB-CS      : Anita Rao\n";
echo "   •  1  CB-IT      : Bimal Shah\n";
echo "🏛  Classrooms  : 4\n";
echo "🔬 CS Labs : 6  |  IT Labs : 6  (no cross-dept room sharing)\n";
echo "📚 Subjects : 16\n";
echo "   CS Core : Data Structures & Algorithms | Computer Organization | Compiler Design\n";
echo "             Computer Graphics | Artificial Intelligence | Cryptography & Network Security\n";
echo "   IT Core : Database Management Systems | Web Development | Cloud Computing\n";
echo "             Mobile Application Development | Software Testing & QA | Network Administration\n";
echo "   + L&D × 2  |  Creative Break × 2\n";
echo "\n⚙️  Run '/generate' to build the timetable!\n\n";
