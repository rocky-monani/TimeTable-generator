<?php
// storage/test_batch_data.php

use Illuminate\Support\Facades\DB;

DB::statement('SET FOREIGN_KEY_CHECKS=0;');
DB::table('timetables')->truncate();
DB::table('subject_labs')->truncate();
DB::table('section_rooms')->truncate();
DB::table('subjects')->truncate();
DB::table('sections')->truncate();
DB::table('departments')->truncate();
DB::table('faculties')->truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');

// Departments
$deptId = DB::table('departments')->insertGetId([
    'name' => 'Computer Science',
    'created_at' => now(), 'updated_at' => now()
]);

// Section with batches = 3
$sectionId = DB::table('sections')->insertGetId([
    'name' => 'A - Batch Test',
    'semester' => 4,
    'departmentId' => $deptId,
    'batches' => 3,
    'created_at' => now(), 'updated_at' => now()
]);

// Faculties
$f1 = DB::table('faculties')->insertGetId(['name' => 'Prof. Alan Turing (Lab 1)', 'weeklyLoad' => 20, 'maxDailyHours' => 10, 'created_at' => now(), 'updated_at' => now()]);
$f2 = DB::table('faculties')->insertGetId(['name' => 'Prof. Ada Lovelace (Lab 2)', 'weeklyLoad' => 20, 'maxDailyHours' => 10, 'created_at' => now(), 'updated_at' => now()]);
$f3 = DB::table('faculties')->insertGetId(['name' => 'Prof. Grace Hopper (Lab 3)', 'weeklyLoad' => 20, 'maxDailyHours' => 10, 'created_at' => now(), 'updated_at' => now()]);
$f4 = DB::table('faculties')->insertGetId(['name' => 'Prof. John von Neumann (Theory)', 'weeklyLoad' => 20, 'maxDailyHours' => 10, 'created_at' => now(), 'updated_at' => now()]);

// Rooms
$lectureRoom = DB::table('rooms')->insertGetId(['name' => 'Lecture Hall 1', 'type' => 'classroom', 'created_at' => now(), 'updated_at' => now()]);
$lab1 = DB::table('rooms')->insertGetId(['name' => 'Computer Lab 1', 'type' => 'lab', 'created_at' => now(), 'updated_at' => now()]);
$lab2 = DB::table('rooms')->insertGetId(['name' => 'Computer Lab 2', 'type' => 'lab', 'created_at' => now(), 'updated_at' => now()]);
$lab3 = DB::table('rooms')->insertGetId(['name' => 'Hardware Lab 3', 'type' => 'lab', 'created_at' => now(), 'updated_at' => now()]);

// Assign Room to section
DB::table('section_rooms')->insert([
    'section_id' => $sectionId,
    'room_id' => $lectureRoom,
    'created_at' => now(), 'updated_at' => now()
]);

// Subjects (3 Labs, 1 Lecture)
$subLab1 = DB::table('subjects')->insertGetId([
    'name' => 'Database Lab', 'subjectCode' => 'CS401', 'semester' => 4, 'departmentId' => $deptId,
    'theoryHoursPerWeek' => 0, 'practicalHoursPerWeek' => 2, 'faculty_id' => $f1
]);
$subLab2 = DB::table('subjects')->insertGetId([
    'name' => 'Networking Lab', 'subjectCode' => 'CS402', 'semester' => 4, 'departmentId' => $deptId,
    'theoryHoursPerWeek' => 0, 'practicalHoursPerWeek' => 2, 'faculty_id' => $f2
]);
$subLab3 = DB::table('subjects')->insertGetId([
    'name' => 'OS Lab', 'subjectCode' => 'CS403', 'semester' => 4, 'departmentId' => $deptId,
    'theoryHoursPerWeek' => 0, 'practicalHoursPerWeek' => 2, 'faculty_id' => $f3
]);

$subLec1 = DB::table('subjects')->insertGetId([
    'name' => 'Theory Of Computation', 'subjectCode' => 'CS404', 'semester' => 4, 'departmentId' => $deptId,
    'theoryHoursPerWeek' => 4, 'practicalHoursPerWeek' => 0, 'faculty_id' => $f4
]);

// Assign Labs to Lab Rooms
DB::table('subject_labs')->insert([
    ['subject_id' => $subLab1, 'room_id' => $lab1],
    ['subject_id' => $subLab2, 'room_id' => $lab2],
    ['subject_id' => $subLab3, 'room_id' => $lab3],
]);

echo "Dummy batch data generated successfully! 1 Section (3 Batches), 3 Lab Subjects, 1 Theory Subject.";
