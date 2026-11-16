<?php
use Illuminate\Support\Facades\DB;

echo "=== CONFLICT ANALYSIS ===\n\n";

// 1. Check faculty busy at same day+slot across sections (lecture conflicts)
echo "--- Faculty Double-Booking (Lectures) ---\n";
$conflicts = DB::table('timetables as t1')
    ->join('timetables as t2', function($j) {
        $j->on('t1.faculty_id', '=', 't2.faculty_id')
          ->on('t1.day_id', '=', 't2.day_id')
          ->on('t1.slot_no', '=', 't2.slot_no')
          ->on('t1.section_id', '!=', 't2.section_id')
          ->where('t1.id', '<', DB::raw('t2.id'));
    })
    ->join('faculties', 't1.faculty_id', '=', 'faculties.id')
    ->join('sections as s1', 't1.section_id', '=', 's1.id')
    ->join('sections as s2', 't2.section_id', '=', 's2.id')
    ->join('week_days', 't1.day_id', '=', 'week_days.id')
    ->select('faculties.name as faculty', 'week_days.day_name', 't1.slot_no', 't1.type as type1', 't2.type as type2', 's1.name as sec1', 's2.name as sec2')
    ->get();

if ($conflicts->isEmpty()) {
    echo "  ✅ No faculty double-booking found\n";
} else {
    foreach ($conflicts as $c) {
        echo "  ❌ {$c->faculty} is double-booked on {$c->day_name} Slot {$c->slot_no} → {$c->sec1} ({$c->type1}) & {$c->sec2} ({$c->type2})\n";
    }
}

// 2. Check room double-booking
echo "\n--- Room Double-Booking ---\n";
$roomConflicts = DB::table('timetables as t1')
    ->join('timetables as t2', function($j) {
        $j->on('t1.classroom_id', '=', 't2.classroom_id')
          ->on('t1.day_id', '=', 't2.day_id')
          ->on('t1.slot_no', '=', 't2.slot_no')
          ->on('t1.section_id', '!=', 't2.section_id')
          ->where('t1.id', '<', DB::raw('t2.id'));
    })
    ->join('rooms', 't1.classroom_id', '=', 'rooms.id')
    ->join('sections as s1', 't1.section_id', '=', 's1.id')
    ->join('sections as s2', 't2.section_id', '=', 's2.id')
    ->join('week_days', 't1.day_id', '=', 'week_days.id')
    ->select('rooms.name as room', 'week_days.day_name', 't1.slot_no', 't1.type as type1', 't2.type as type2', 's1.name as sec1', 's2.name as sec2')
    ->get();

if ($roomConflicts->isEmpty()) {
    echo "  ✅ No room double-booking found\n";
} else {
    foreach ($roomConflicts as $c) {
        echo "  ❌ Room [{$c->room}] double-booked on {$c->day_name} Slot {$c->slot_no} → {$c->sec1} ({$c->type1}) & {$c->sec2} ({$c->type2})\n";
    }
}

// 3. Missing batches per section
echo "\n--- Missing Lab Batches ---\n";
$sections = DB::table('sections')->get();
foreach ($sections as $sec) {
    $subjects = DB::table('subjects')
        ->where('semester', $sec->semester)
        ->where('departmentId', $sec->departmentId)
        ->where('practicalHoursPerWeek', '>', 0)
        ->whereRaw("name NOT LIKE '%Creative%'")
        ->get();
    foreach ($subjects as $subj) {
        $batchesScheduled = DB::table('timetables')
            ->where('section_id', $sec->id)
            ->where('subject_id', $subj->id)
            ->where('type', 'lab')
            ->whereNotNull('batch')
            ->distinct()->pluck('batch')->toArray();
        $expected = range(1, $sec->batches);
        $missing = array_diff($expected, $batchesScheduled);
        if (!empty($missing)) {
            echo "  ❌ Section [{$sec->name}] Subject [{$subj->name}] → Missing batch(es): ".implode(', ', $missing)."\n";
        }
    }
}

// 4. Theory hours coverage
echo "\n--- Theory Hours Coverage ---\n";
$sections = DB::table('sections')->get();
foreach ($sections as $sec) {
    $subjects = DB::table('subjects')
        ->where('semester', $sec->semester)
        ->where('departmentId', $sec->departmentId)
        ->where('theoryHoursPerWeek', '>', 0)
        ->whereRaw("name NOT LIKE '%Creative%'")
        ->get();
    foreach ($subjects as $subj) {
        $scheduled = DB::table('timetables')
            ->where('section_id', $sec->id)
            ->where('subject_id', $subj->id)
            ->where('type', 'lecture')
            ->count();
        if ($scheduled < $subj->theoryHoursPerWeek) {
            echo "  ❌ Section [{$sec->name}] Subject [{$subj->name}] → Theory: Expected {$subj->theoryHoursPerWeek}, Got {$scheduled}\n";
        }
    }
}

echo "\n=== ANALYSIS COMPLETE ===\n";
