<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TimetableGeneratorService
{
    // ========================= PRE-GENERATION VALIDATION =========================

    public function validate($targetSectionId = null): array
    {
        $errors = [];
        $days       = DB::table('week_days')->count();
        $slotPairs  = 3; // Slot pairs: [7,8], [4,5], [1,2]
        $totalSlots = $days * $slotPairs; // e.g. 6 days × 3 pairs = 18 max possible lab sessions per section

        $sections = $targetSectionId
            ? DB::table('sections')->where('id', $targetSectionId)->get()
            : DB::table('sections')->get();

        foreach ($sections as $section) {
            $prefix     = "Section [{$section->name}]";
            $batchCount = max(1, $section->batches ?? 1);

            // ── 1. Section must have a classroom ──────────────────────────────────────
            $room = DB::table('section_rooms')->where('section_id', $section->id)->first();
            if (!$room) {
                $errors[] = [
                    'type'       => 'error',
                    'section'    => $section->name,
                    'message'    => "$prefix has no classroom assigned. Lectures cannot be scheduled.",
                    'suggestion' => "Go to [Section → Room Mapping] and assign an available classroom to '{$section->name}'. "
                                  . "If no classroom is free, add a new classroom under [Rooms Management].",
                ];
            }

            // ── 2. Fetch subjects for this section ────────────────────────────────────
            $subjects = DB::table('subjects')
                ->where('semester', $section->semester)
                ->where('departmentId', $section->departmentId)
                ->get();

            $labSessions      = 0;
            $labFacultyUsage  = []; // fid => sessions count (across all subjects this section)

            foreach ($subjects as $subj) {
                $isCreative = stripos($subj->name, 'Creative') !== false;
                $isLD       = stripos($subj->name, 'L&D')      !== false;

                // ── 3. Lab room missing ───────────────────────────────────────────────
                if ($subj->practicalHoursPerWeek > 0 && !$isCreative) {
                    $labRoom = DB::table('subject_labs')->where('subject_id', $subj->id)->first();
                    if (!$labRoom) {
                        $errors[] = [
                            'type'       => 'error',
                            'section'    => $section->name,
                            'message'    => "$prefix → '{$subj->name}' has no lab room assigned. "
                                          . "Lab sessions cannot be scheduled for this subject.",
                            'suggestion' => "Option A: Go to [Subject-Lab Mapping] and assign an existing lab room to '{$subj->name}'. "
                                          . "Option B: If no suitable lab exists, first create a new lab room under [Rooms Management] (type = lab), "
                                          . "then map it to this subject.",
                        ];
                    }

                    // ── 4. Lab faculty missing ────────────────────────────────────────
                    if (!$subj->faculty_id) {
                        $errors[] = [
                            'type'       => 'error',
                            'section'    => $section->name,
                            'message'    => "$prefix → '{$subj->name}' has no faculty assigned. "
                                          . "Neither theory lectures nor lab sessions can be scheduled.",
                            'suggestion' => "Go to [Subjects] and assign a faculty to '{$subj->name}'. "
                                          . "If all faculty are overloaded, add a new faculty member under [Faculty Management] "
                                          . "and assign them to this subject.",
                        ];
                    }

                    // Accumulate lab sessions per faculty
                    $labFid = $subj->faculty_id_2 ?? $subj->faculty_id;
                    if ($labFid) {
                        $labFacultyUsage[$labFid] = ($labFacultyUsage[$labFid] ?? 0) + $batchCount;
                    }

                    $labSessions += $batchCount; // each batch needs 1 lab session slot-pair
                }

                // ── 5. Theory faculty missing ─────────────────────────────────────────
                if ($subj->theoryHoursPerWeek > 0 && !$isCreative && !$isLD) {
                    if (!$subj->faculty_id) {
                        $errors[] = [
                            'type'       => 'error',
                            'section'    => $section->name,
                            'message'    => "$prefix → '{$subj->name}' has no theory faculty assigned. "
                                          . "Lecture slots will be empty.",
                            'suggestion' => "Go to [Subjects] → Edit '{$subj->name}' and assign a theory faculty. "
                                          . "If all faculty are at capacity, either: "
                                          . "(1) Increase the weekly load of an existing faculty under [Faculty Management], or "
                                          . "(2) Add a new faculty member and assign them here.",
                        ];
                    }
                }

                // ── 6. L&D subject faculty missing ───────────────────────────────────
                if ($isLD && !$subj->faculty_id) {
                    $errors[] = [
                        'type'       => 'error',
                        'section'    => $section->name,
                        'message'    => "$prefix → L&D subject '{$subj->name}' has no faculty. "
                                      . "It will be skipped in the timetable.",
                        'suggestion' => "Go to [Subjects] → Edit '{$subj->name}' and assign a faculty. "
                                      . "L&D is placed at a fixed slot — any available faculty with a light load can take it.",
                    ];
                }
            }

            // ── 7. Total lab capacity check ───────────────────────────────────────────
            // Each section needs `labSessions` slot-pairs per week.
            // There are only `totalSlots` possible slot-pairs per section per week.
            if ($labSessions > $totalSlots) {
                $surplus = $labSessions - $totalSlots;
                $errors[] = [
                    'type'       => 'warning',
                    'section'    => $section->name,
                    'message'    => "$prefix requires $labSessions lab sessions per week "
                                  . "but only $totalSlots slot-pairs are available across $days working days "
                                  . "($surplus sessions CANNOT be scheduled).",
                    'suggestion' => "Since subject hours and weekdays are fixed, you have these options: "
                                  . "(1) [Best] Add more lab rooms and assign a second lab faculty per subject "
                                  . "— this lets 2 batches run in the SAME slot pair, cutting required slots in half. "
                                  . "(2) Increase the 'Max Daily Hours' of lab faculty under [Faculty Management] "
                                  . "so they can cover more sessions per day. "
                                  . "(3) Add new lab faculty members and split the subject labs between them "
                                  . "to eliminate scheduling conflicts.",
                ];
            }

            // ── 8. Per-faculty lab overload ───────────────────────────────────────────
            // A single faculty cannot teach more lab sessions than total available slot-pairs
            foreach ($labFacultyUsage as $fid => $needed) {
                if ($needed > $totalSlots) {
                    $fName   = DB::table('faculties')->where('id', $fid)->value('name') ?? "Faculty #$fid";
                    $surplus = $needed - $totalSlots;
                    $errors[] = [
                        'type'       => 'warning',
                        'section'    => $section->name,
                        'message'    => "$prefix → Lab faculty '$fName' is assigned $needed lab sessions "
                                      . "but only $totalSlots slot-pairs exist per week ($surplus sessions will be missed).",
                        'suggestion' => "Options to fix '$fName' overload: "
                                      . "(1) [Recommended] In [Subjects], assign a different dedicated lab faculty (Lab Faculty field) "
                                      . "to some subjects currently using '$fName' — spreading the load. "
                                      . "(2) Under [Faculty Management], increase '$fName's Max Daily Hours "
                                      . "so they can cover multiple lab sessions on the same day. "
                                      . "(3) Add a new lab faculty member and reassign some subjects' Lab Faculty to them.",
                    ];
                }
            }

            // ── 9. Check: will enough THEORY slots be available? ──────────────────────
            // A section has (days × available lecture slots) lectures minus reserved slots for L&D/CB
            $totalTheoryHrs = $subjects
                ->where('practicalHoursPerWeek', 0)
                ->sum('theoryHoursPerWeek')
                + $subjects->where('practicalHoursPerWeek', '>', 0)->sum('theoryHoursPerWeek');

            // Conservative: 8 slots/day - 2 lab slots = 6 usable lecture slots × 6 days = 36
            $maxTheorySlots = $days * 6;
            if ($totalTheoryHrs > $maxTheorySlots) {
                $errors[] = [
                    'type'       => 'warning',
                    'section'    => $section->name,
                    'message'    => "$prefix has $totalTheoryHrs total theory hours needed "
                                  . "but only ~$maxTheorySlots lecture slots are realistically available per week.",
                    'suggestion' => "Options: "
                                  . "(1) Increase the Max Daily Hours for theory faculty under [Faculty Management] "
                                  . "so they can take more lectures per day. "
                                  . "(2) Assign additional faculty members to share the theory load "
                                  . "across more slots. "
                                  . "(3) Review if any subject's theory hours are conflicting — all must fit within the week.",
                ];
            }
        }

        // ── 10. Cross-section lab faculty load check ─────────────────────────────────
        // If NOT filtering by section, check faculty overload across ALL sections
        if (!$targetSectionId) {
            $allSubjects    = DB::table('subjects')->get();
            $allSections    = DB::table('sections')->get();
            $globalFaculty  = [];

            foreach ($allSections as $sec) {
                $secSubjects = $allSubjects
                    ->where('semester', $sec->semester)
                    ->where('departmentId', $sec->departmentId);
                $bc = max(1, $sec->batches ?? 1);
                foreach ($secSubjects as $s) {
                    if ($s->practicalHoursPerWeek > 0 && stripos($s->name, 'Creative') === false) {
                        $fid = $s->faculty_id_2 ?? $s->faculty_id;
                        if ($fid) {
                            $globalFaculty[$fid][] = ['section' => $sec->name, 'subject' => $s->name, 'batches' => $bc];
                        }
                    }
                }
            }

            foreach ($globalFaculty as $fid => $assignments) {
                $totalSessions = array_sum(array_column($assignments, 'batches'));
                // Safe threshold: a faculty can realistically cover 70% of total slots
                // (30% blocked by lecture scheduling, section conflicts, etc.)
                $safeThreshold = (int) floor($totalSlots * 0.70); // e.g. 18 * 0.70 = 12
                if ($totalSessions > $safeThreshold) {
                    $fName    = DB::table('faculties')->where('id', $fid)->value('name') ?? "Faculty #$fid";
                    $sections = implode(', ', array_unique(array_column($assignments, 'section')));
                    $subjects = implode(', ', array_unique(array_column($assignments, 'subject')));
                    $errors[] = [
                        'type'       => 'warning',
                        'section'    => $sections,
                        'message'    => "Lab faculty '$fName' is responsible for $totalSessions lab sessions "
                                      . "across sections [$sections] for subjects [$subjects]. "
                                      . "This exceeds the safe scheduling threshold of ~$safeThreshold sessions.",
                        'suggestion' => "This is the most common cause of incomplete timetables. Options: "
                                      . "(1) [Best Fix] In [Subjects], assign a SEPARATE dedicated lab faculty (Lab Faculty field) "
                                      . "to each subject so no single faculty teaches across multiple sections. "
                                      . "(2) Under [Faculty Management], increase '$fName's Max Daily Hours so they can "
                                      . "handle more sessions per day. "
                                      . "(3) Add new lab faculty members — at minimum, one lab faculty per subject per department pair "
                                      . "(e.g., CS-DBMS-Lab and IT-WebTech-Lab should not share the same faculty).",
                    ];
                }
            }
        }

        return $errors;
    }


    // ========================= GENERATION =========================

    public function generate($targetSectionId = null)
    {
        DB::enableQueryLog();

        // If targetSectionId is provided, only clear and generate for that section
        if ($targetSectionId) {
            DB::table('timetables')->where('section_id', $targetSectionId)->delete();
            $sections = DB::table('sections')->where('id', $targetSectionId)->get();
        } else {
            // Otherwise, safe clear EVERYTHING and generate all
            DB::table('timetables')->truncate();
            $sections = DB::table('sections')->get();
        }

        foreach ($sections as $section) {
            $lectureTracker = [];
            $labTracker     = [];

            // Get room assigned to this section
            $room = $this->getRoom($section->id) ?? 1;

            $subjects = DB::table('subjects')
                ->where('semester', $section->semester)
                ->where('departmentId', $section->departmentId)
                ->get();

            // Find specific subjects for THIS section/department
            $ldSubj = $subjects->first(function($s) { return stripos($s->name, 'L&D') !== false; });
            $cbSubj = $subjects->first(function($s) { return stripos($s->name, 'Creative') !== false; });

            // Tracked per Section per Day to enforce "No Repeat" rules
            $dailyLectures = []; 
            $dailyLabs     = []; 

            // Initialize trackers for ALL subjects in the section
            foreach ($subjects as $subject) {
                $lectureTracker[$subject->id] = 0;
                $labTracker[$subject->id] = 0;
            }

            // 1. FORCE L&D (Only if it exists for this department)
            if ($ldSubj && $ldSubj->faculty_id) {
                $this->insertFixedLecture($section, $ldSubj, 1, 2, $room, $lectureTracker, true);
                $this->insertFixedLecture($section, $ldSubj, 5, 2, $room, $lectureTracker, true);
                $dailyLectures[1][] = $ldSubj->id;
                $dailyLectures[5][] = $ldSubj->id;
            }

            // 2. FORCE Creative Break (Only if it exists for this department)
            if ($cbSubj) {
                $labRoom = $this->getLabRoom($cbSubj->id) ?? $room;
                $this->insertLab($section->id, $cbSubj, $labRoom, 4, 4, 5);
                $dailyLabs[4][] = $cbSubj->id;
                $labTracker[$cbSubj->id] += 2;
            }

            $days  = DB::table('week_days')->get();
            $slots = DB::table('time_slots')->where('type', 'lecture')->orderBy('slot_no')->get();

            $batchCount = $section->batches > 0 ? $section->batches : 1;

            // =========================
            // LAB QUEUE
            // =========================
            $labQueue = [];
            foreach ($subjects as $subject) {
                if (!$subject->faculty_id || $subject->name == 'Creative Break') continue;
                $hours = $subject->practicalHoursPerWeek ?? 0;
                while ($hours > 0) {
                    for ($b = 1; $b <= $batchCount; $b++) {
                        $labQueue[] = [
                            'subject' => $subject,
                            'batch' => $b
                        ];
                    }
                    $hours -= 2;
                }
            }

            // =========================
            // ASSIGN LABS
            // =========================
            foreach ($days as $day) {
                if (empty($labQueue)) break;

                // Allow up to 3 lab slot-pairs per day (one at [7,8], one at [4,5], one at [1,2])
                // Creative Break occupies [4,5] on day 4, so that day effectively has 2 remaining pairs
                $labsToday = isset($dailyLabs[$day->id]) ? count($dailyLabs[$day->id]) : 0;
                $maxLabs = 3;
                if ($labsToday >= $maxLabs) continue;

                $pairs = [[7, 8], [4, 5], [1, 2]];
                foreach ($pairs as [$s1, $s2]) {
                    if ($this->isSectionBusy($section->id, $day->id, $s1) || $this->isSectionBusy($section->id, $day->id, $s2)) {
                        continue;
                    }

                    $selectedGroups = [];
                    $selectedKeys = [];
                    $assignedFaculties = [];
                    $assignedRooms = [];
                    $assignedBatches = [];

                    foreach ($labQueue as $key => $item) {
                        $subject = $item['subject'];
                        $b = $item['batch'];

                        if (count($selectedGroups) == $batchCount) break;

                        if (in_array($b, $assignedBatches)) continue;

                        $labRoom = $this->getLabRoom($subject->id);
                        if (!$labRoom) continue;

                        $labFacultyId = $subject->faculty_id_2 ?? $subject->faculty_id;

                        if (in_array($labFacultyId, $assignedFaculties) || in_array($labRoom, $assignedRooms)) continue;

                        if (
                            !$this->isFacultyBusy($labFacultyId, $day->id, $s1) &&
                            !$this->isFacultyBusy($labFacultyId, $day->id, $s2) &&
                            !$this->isFacultyUnavailable($labFacultyId, $day->id, $s1) &&
                            !$this->isFacultyUnavailable($labFacultyId, $day->id, $s2) &&
                            !$this->isFacultyOverloaded($labFacultyId, $day->id) &&
                            !$this->isLabRoomBusy($labRoom, $day->id, $s1) &&
                            !$this->isLabRoomBusy($labRoom, $day->id, $s2)
                        ) {
                            $selectedGroups[] = $item;
                            $selectedKeys[] = $key;
                            $assignedBatches[] = $b;
                            $assignedFaculties[] = $labFacultyId;
                            $assignedRooms[] = $labRoom;
                        }
                    }

                    if (count($selectedGroups) > 0) {
                        foreach ($selectedGroups as $k => $item) {
                            $subject = $item['subject'];
                            $b = $item['batch'];
                            
                            $labRoom = $this->getLabRoom($subject->id);
                            $labFacultyId = $subject->faculty_id_2 ?? $subject->faculty_id;
                            $this->insertLab($section->id, $subject, $labRoom, $day->id, $s1, $s2, $batchCount > 1 ? $b : null, $labFacultyId);
                            $dailyLabs[$day->id][] = $subject->id;
                            
                            unset($labQueue[$selectedKeys[$k]]);
                        }
                        
                        $labQueue = array_values($labQueue);
                        break; // Only 1 lab slot allocated collectively per day
                    }
                }
            }

            // =========================
            // LECTURES
            // =========================
            foreach ($slots as $slot) {
                foreach ($days as $day) {
                    if ($this->isSectionBusy($section->id, $day->id, $slot->slot_no)) continue;

                    foreach ($subjects as $subject) {
                        if (!$subject->faculty_id) continue;

                        if ($lectureTracker[$subject->id] >= ($subject->theoryHoursPerWeek ?? 2))
                            continue;

                        // No 2 lectures of same subject on same day
                        if (isset($dailyLectures[$day->id]) && in_array($subject->id, $dailyLectures[$day->id]))
                            continue;

                        if ($this->isSubjectAlreadyInSlot($subject->id, $slot->slot_no, $section->id, $day->id))
                            continue;

                        if (
                            !$this->isFacultyBusy($subject->faculty_id, $day->id, $slot->slot_no) &&
                            !$this->isFacultyUnavailable($subject->faculty_id, $day->id, $slot->slot_no) &&
                            !$this->isFacultyOverloaded($subject->faculty_id, $day->id) &&
                            !$this->isRoomBusy($section->id, $day->id, $slot->slot_no)
                        ) {
                            DB::table('timetables')->insert([
                                'section_id'   => $section->id,
                                'subject_id'   => $subject->id,
                                'faculty_id'   => $subject->faculty_id,
                                'classroom_id' => $this->getRoom($section->id),
                                'day_id'       => $day->id,
                                'slot_no'      => $slot->slot_no,
                                'type'         => 'lecture',
                                'created_at'   => now(),
                                'updated_at'   => now()
                            ]);

                            $lectureTracker[$subject->id]++;
                            $dailyLectures[$day->id][] = $subject->id;
                            break;
                        }
                    }
                }
            }
        }

        // Return post-generation warnings so the controller can flash them
        return $this->postValidate($targetSectionId);
    }

    // ========================= POST-GENERATION AUDIT =========================

    public function postValidate($targetSectionId = null): array
    {
        $warnings = [];
        $days     = DB::table('week_days')->count();

        $sections = $targetSectionId
            ? DB::table('sections')->where('id', $targetSectionId)->get()
            : DB::table('sections')->get();

        foreach ($sections as $section) {
            $prefix     = "Section [{$section->name}]";
            $batchCount = max(1, $section->batches ?? 1);

            $subjects = DB::table('subjects')
                ->where('semester', $section->semester)
                ->where('departmentId', $section->departmentId)
                ->get();

            foreach ($subjects as $subj) {
                $isCreative = stripos($subj->name, 'Creative') !== false;
                $isLD       = stripos($subj->name, 'L&D')      !== false;

                // ── Check: all batches scheduled for lab subjects ─────────────────────
                if ($subj->practicalHoursPerWeek > 0 && !$isCreative) {
                    $scheduledBatches = DB::table('timetables')
                        ->where('section_id', $section->id)
                        ->where('subject_id', $subj->id)
                        ->where('type', 'lab')
                        ->whereNotNull('batch')
                        ->distinct()->pluck('batch')->toArray();

                    $missingBatches = array_diff(range(1, $batchCount), $scheduledBatches);
                    if (!empty($missingBatches)) {
                        $missing = implode(', ', $missingBatches);
                        $fid     = $subj->faculty_id_2 ?? $subj->faculty_id;
                        $fName   = DB::table('faculties')->where('id', $fid)->value('name') ?? "Unassigned";
                        $warnings[] = [
                            'type'       => 'warning',
                            'section'    => $section->name,
                            'message'    => "$prefix → '{$subj->name}' is MISSING lab sessions for batch(es): $missing. "
                                          . "Lab faculty '$fName' had no free slot-pair available at generation time.",
                            'suggestion' => "The scheduler ran out of non-conflicting slots for '$fName'. Options: "
                                          . "(1) [Best Fix] Assign a dedicated lab faculty (Lab Faculty field in [Subjects]) "
                                          . "exclusively for '{$subj->name}' in '{$section->name}' — "
                                          . "shared faculty across sections causes this conflict. "
                                          . "(2) Under [Faculty Management], increase '$fName\'s Max Daily Hours "
                                          . "so they can take more lab sessions per day and free up later slots. "
                                          . "(3) Add a new lab faculty member and assign them specifically to "
                                          . "'{$subj->name}' in one section, giving '$fName' fewer sessions to cover.",
                        ];
                    }
                }

                // ── Check: full theory hours covered ─────────────────────────────────
                if ($subj->theoryHoursPerWeek > 0 && !$isCreative) {
                    $scheduledTheory = DB::table('timetables')
                        ->where('section_id', $section->id)
                        ->where('subject_id', $subj->id)
                        ->where('type', 'lecture')
                        ->count();

                    if ($scheduledTheory < $subj->theoryHoursPerWeek) {
                        $gap   = $subj->theoryHoursPerWeek - $scheduledTheory;
                        $fName = DB::table('faculties')->where('id', $subj->faculty_id)->value('name') ?? 'Unassigned';
                        $warnings[] = [
                            'type'       => 'warning',
                            'section'    => $section->name,
                            'message'    => "$prefix → '{$subj->name}' only has $scheduledTheory of {$subj->theoryHoursPerWeek} "
                                          . "required theory lectures scheduled ($gap lecture(s) missing). "
                                          . "Faculty '$fName' had no free lecture slots available.",
                            'suggestion' => "The scheduler could not fit all theory hours due to slot competition. Options: "
                                          . "(1) Under [Faculty Management], increase '$fName\'s Max Daily Hours "
                                          . "so they are available for more slots per day. "
                                          . "(2) If '$fName' teaches many sections, add a new theory faculty member "
                                          . "and reassign '{$subj->name}' in '{$section->name}' to them — "
                                          . "reducing '$fName\'s cross-section load. "
                                          . "(3) Check in [Faculty Availability] whether any days are marked unavailable for '$fName' "
                                          . "and unblock them if appropriate.",
                        ];
                    }
                }
            }

            // ── Check: real faculty double-booking (excluding intentional shared dept events) ──
            // L&D and Creative Break are dept-wide shared activities — both sections A and B
            // intentionally attend the same faculty at the same slot. Skip those.
            $sharedSubjectIds = DB::table('subjects')
                ->where(function($q) {
                    $q->where('name', 'LIKE', '%L&D%')
                      ->orWhere('name', 'LIKE', '%Creative%');
                })
                ->pluck('id');

            $doubleBooked = DB::table('timetables as t1')
                ->join('timetables as t2', function($j) {
                    $j->on('t1.faculty_id', '=', 't2.faculty_id')
                      ->on('t1.day_id', '=', 't2.day_id')
                      ->on('t1.slot_no', '=', 't2.slot_no')
                      ->on('t1.section_id', '!=', 't2.section_id')
                      ->where('t1.id', '<', DB::raw('t2.id'));
                })
                ->join('faculties', 't1.faculty_id', '=', 'faculties.id')
                ->join('sections as s2', 't2.section_id', '=', 's2.id')
                ->join('week_days', 't1.day_id', '=', 'week_days.id')
                ->where('t1.section_id', $section->id)
                // Exclude L&D and Creative Break — they are dept-wide shared slots (by design)
                ->whereNotIn('t1.subject_id', $sharedSubjectIds)
                ->whereNotIn('t2.subject_id', $sharedSubjectIds)
                ->select('faculties.name as faculty', 'week_days.day_name', 't1.slot_no', 't1.type', 's2.name as other_section', 't1.subject_id')
                ->distinct()
                ->get();

            foreach ($doubleBooked as $d) {
                $warnings[] = [
                    'type'       => 'warning',
                    'section'    => $section->name,
                    'message'    => "$prefix → Faculty '{$d->faculty}' is double-booked on {$d->day_name} Slot {$d->slot_no} "
                                  . "with section [{$d->other_section}] ({$d->type}). "
                                  . "This is a real conflict — both sections' students cannot have the same faculty at the same time.",
                    'suggestion' => "This subject's faculty is shared between '{$section->name}' and '{$d->other_section}' at the same slot. Options: "
                                  . "(1) In [Subjects], assign a DIFFERENT faculty for this subject in one of the two sections "
                                  . "so each section has its own dedicated faculty. "
                                  . "(2) Add a new faculty member under [Faculty Management] and assign them to one section's subject. "
                                  . "(3) If both sections must share this faculty, increase their Max Daily Hours "
                                  . "so the generator finds non-overlapping slots for each section.",
                ];
            }

        }

        return $warnings;
    }

    // ========================= HELPERS =========================

    private function insertFixedLecture($section, $subject, $dayId, $slotNo, $room, &$tracker, $force = false, $type = 'lecture')
    {
        if ($force || (
            !$this->isSectionBusy($section->id, $dayId, $slotNo) &&
            !$this->isFacultyBusy($subject->faculty_id, $dayId, $slotNo)
        )) {
            DB::table('timetables')->insert([
                'section_id'   => $section->id,
                'subject_id'   => $subject->id,
                'faculty_id'   => $subject->faculty_id,
                'classroom_id' => $room,
                'day_id'       => $dayId,
                'slot_no'      => $slotNo,
                'type'         => $type,
                'created_at'   => now(),
                'updated_at'   => now()
            ]);
            $tracker[$subject->id]++;
        }
    }

    private function insertLab($sectionId, $subject, $labRoom, $dayId, $s1, $s2, $batch = null, $labFacultyId = null)
    {
        $facultyId = $labFacultyId ?? $subject->faculty_id_2 ?? $subject->faculty_id;

        DB::table('timetables')->insert([
            [
                'section_id'   => $sectionId,
                'subject_id'   => $subject->id,
                'faculty_id'   => $facultyId,
                'classroom_id' => $labRoom,
                'day_id'       => $dayId,
                'slot_no'      => $s1,
                'type'         => 'lab',
                'batch'        => $batch,
                'created_at'   => now(),
                'updated_at'   => now()
            ],
            [
                'section_id'   => $sectionId,
                'subject_id'   => $subject->id,
                'faculty_id'   => $facultyId,
                'classroom_id' => $labRoom,
                'day_id'       => $dayId,
                'slot_no'      => $s2,
                'type'         => 'lab',
                'batch'        => $batch,
                'created_at'   => now(),
                'updated_at'   => now()
            ]
        ]);
    }

    private function isSectionBusy($section, $day, $slot)
    {
        return DB::table('timetables')
            ->where('section_id', $section)
            ->where('day_id', $day)
            ->where('slot_no', $slot)
            ->exists();
    }

    private function isSubjectAlreadyInSlot($subject, $slot_no, $section, $day_id)
    {
        if ($day_id == 1) return false;

        return DB::table('timetables')
            ->where('section_id', $section)
            ->where('subject_id', $subject)
            ->where('slot_no', $slot_no)
            ->where(function ($query) use ($day_id) {
                if ($day_id > 2) {
                    $query->where('day_id', $day_id - 1)
                          ->orWhere('day_id', $day_id - 2);
                } else {
                    $query->where('day_id', $day_id - 1);
                }
            })
            ->where('type', 'lecture')
            ->exists();
    }

    private function isFacultyBusy($faculty, $day, $slot)
    {
        return DB::table('timetables')
            ->where('faculty_id', $faculty)
            ->where('day_id', $day)
            ->where('slot_no', $slot)
            ->exists();
    }

    private function isFacultyUnavailable($faculty, $day, $slot)
    {
        return DB::table('faculty_availabilities')
            ->where('faculty_id', $faculty)
            ->where('day_id', $day)
            ->where('slot_no', $slot)
            ->exists();
    }

    private function isFacultyOverloaded($faculty, $day)
    {
        $count = DB::table('timetables')
            ->where('faculty_id', $faculty)
            ->where('day_id', $day)
            ->count();

        $max = DB::table('faculties')
            ->where('id', $faculty)
            ->value('maxDailyHours');

        return $max ? $count >= $max : false;
    }

    private function isRoomBusy($section, $day, $slot)
    {
        $room = $this->getRoom($section);
        return $room ? $this->isRoomBusyById($room, $day, $slot) : false;
    }

    private function isRoomBusyById($room, $day, $slot)
    {
        return DB::table('timetables')
            ->where('classroom_id', $room)
            ->where('day_id', $day)
            ->where('slot_no', $slot)
            ->exists();
    }

    private function isLabRoomBusy($room, $day, $slot)
    {
        return DB::table('timetables')
            ->where('classroom_id', $room)
            ->where('day_id', $day)
            ->where('slot_no', $slot)
            ->exists();
    }

    private function getRoom($section)
    {
        return DB::table('section_rooms')
            ->where('section_id', $section)
            ->value('room_id');
    }

    private function getLabRoom($subject)
    {
        return DB::table('subject_labs')
            ->where('subject_id', $subject)
            ->value('room_id');
    }
}