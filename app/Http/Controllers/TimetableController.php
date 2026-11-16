<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    /**
     * Display the timetable for all sections
     */
    public function index()
    {
        // Get all sections with department info
        $sections = DB::table('sections')
            ->join('departments', 'sections.departmentId', '=', 'departments.id')
            ->select('sections.id', 'sections.name', 'sections.semester', 'departments.name as department_name')
            ->orderBy('sections.name')
            ->get();
            
        $faculties = DB::table('faculties')->orderBy('name')->get();
        $rooms = DB::table('rooms')->orderBy('name')->get();

        // Get the first section to display by default
        $selectedSectionId = $sections->first()->id ?? null;

        return view('timetable.index', compact('sections', 'faculties', 'rooms', 'selectedSectionId'));
    }

    public function reset()
    {
        \DB::table('timetables')->truncate();

        return back()->with('success', 'Timetable cleared');
    }

    /**
     * Manual Edit View
     */
    public function edit($sectionId)
    {
        $section = DB::table('sections')
            ->join('departments', 'sections.departmentId', '=', 'departments.id')
            ->where('sections.id', $sectionId)
            ->select('sections.*', 'departments.name as department_name')
            ->first();

        if (!$section) abort(404);

        $timeSlots = DB::table('time_slots')->orderBy('start_time')->get();
        $weekDays = DB::table('week_days')->orderBy('id')->get();
        
        // Fetch timetable entries for this section with related names
        $timetableData = DB::table('timetables')
            ->join('subjects', 'timetables.subject_id', '=', 'subjects.id')
            ->join('rooms', 'timetables.classroom_id', '=', 'rooms.id')
            ->join('faculties', 'timetables.faculty_id', '=', 'faculties.id')
            ->where('timetables.section_id', $sectionId)
            ->select('timetables.*', 'subjects.name as subject_name', 'rooms.name as room_name', 'faculties.name as faculty_name')
            ->get();

        $structuredData = [];
        $labOverflowSlots = [];

        foreach ($timetableData as $entry) {
            $structuredData[$entry->day_id][$entry->slot_no][] = $entry;
        }

        // Calculate lab overflow slots (2nd hour of labs)
        foreach ($structuredData as $dayId => $slots) {
            ksort($slots);
            $slotNos = array_keys($slots);
            foreach ($slotNos as $idx => $slotNo) {
                $entries = $slots[$slotNo];
                $isLab = false;
                foreach($entries as $e) { if($e->type === 'lab') $isLab = true; }
                if(!$isLab) continue;

                $nextSlotNo = $slotNos[$idx + 1] ?? null;
                if (!$nextSlotNo) continue;
                
                $nextEntries = $slots[$nextSlotNo] ?? [];
                $currIds = collect($entries)->where('type', 'lab')->pluck('subject_id')->unique()->toArray();
                $nextIds = collect($nextEntries)->where('type', 'lab')->pluck('subject_id')->unique()->toArray();

                if (!empty(array_intersect($currIds, $nextIds))) {
                    $labOverflowSlots[$dayId][] = $nextSlotNo;
                }
            }
        }

        // Get subjects for this department/semester OR any "Common" subjects for this semester
        $subjects = DB::table('subjects')
            ->where('semester', $section->semester)
            ->where(function($query) use ($section) {
                $query->where('departmentId', $section->departmentId)
                      ->orWhere('category', 'Common');
            })
            ->orderBy('name')
            ->get();
            
        // Get all rooms for manual override
        $rooms = DB::table('rooms')->orderBy('name')->get();
        
        // Get all faculties to map names in JS
        $faculties = DB::table('faculties')->select('id', 'name')->get();

        return view('timetable.edit', compact('section', 'timeSlots', 'weekDays', 'structuredData', 'subjects', 'rooms', 'faculties', 'labOverflowSlots'));
    }

    /**
     * Update a specific slot manually
     */
    public function updateSlot(Request $request)
    {
        $sectionId = $request->section_id;
        $dayId     = $request->day_id;
        $slotNo    = $request->slot_no;
        $subjectId = $request->subject_id;
        $roomId    = $request->room_id;
        $type      = $request->type ?? 'lecture';

        // 1. Clear existing entry for THIS specific slot in THIS section (Safe)
        DB::table('timetables')
            ->where('section_id', $sectionId)
            ->where('day_id', $dayId)
            ->where('slot_no', $slotNo)
            ->delete();

        // 2. If subjectId is "empty", we are done (slot is now cleared)
        if ($subjectId && $subjectId != 'empty') {
            $subject = DB::table('subjects')->where('id', $subjectId)->first();
            if (!$subject) return response()->json(['success' => false, 'message' => 'Subject not found']);

            $facultyId = $request->faculty_id ?? $subject->faculty_id;

            // 🔍 CONFLICT DETECTION
            
            // Check Faculty clashes
            if ($facultyId) {
                $facultyClash = DB::table('timetables')
                    ->join('sections', 'timetables.section_id', '=', 'sections.id')
                    ->where('timetables.day_id', $dayId)
                    ->where('timetables.slot_no', $slotNo)
                    ->where('timetables.faculty_id', $facultyId)
                    ->where('timetables.section_id', '!=', $sectionId)
                    ->select('sections.name')
                    ->first();

                if ($facultyClash) {
                    return response()->json([
                        'success' => false, 
                        'message' => "CONFLICT: The chosen Faculty is already occupied in section '{$facultyClash->name}' at this time."
                    ], 422);
                }
            }

            // Check Room clashes
            if ($roomId) {
                $roomClash = DB::table('timetables')
                    ->join('sections', 'timetables.section_id', '=', 'sections.id')
                    ->where('timetables.day_id', $dayId)
                    ->where('timetables.slot_no', $slotNo)
                    ->where('timetables.classroom_id', $roomId)
                    ->where('timetables.section_id', '!=', $sectionId)
                    ->where('timetables.type', 'lecture') // Shared labs are often okay
                    ->select('sections.name')
                    ->first();

                if ($roomClash) {
                    return response()->json([
                        'success' => false, 
                        'message' => "CONFLICT: Room is already being used by section '{$roomClash->name}' at this time."
                    ], 422);
                }
            }

            // 3. Insert the new manual entry
            DB::table('timetables')->insert([
                'section_id'   => $sectionId,
                'subject_id'   => $subjectId,
                'faculty_id'   => $facultyId,
                'classroom_id' => $roomId,
                'day_id'       => $dayId,
                'slot_no'      => $slotNo,
                'type'         => $type,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get timetable data for a specific section via AJAX
     */
    public function getData(\Illuminate\Http\Request $request, $id)
    {
        $type = $request->query('type', 'section');

        // Fetch all time slots (including breaks)
        $timeSlots = DB::table('time_slots')
            ->orderBy('start_time')
            ->get();

        // Fetch all week days
        $weekDays = DB::table('week_days')
            ->orderBy('id')
            ->get();

        // Fetch timetable entries for the selected section with all related data
        $query = DB::table('timetables')
            ->join('subjects', 'timetables.subject_id', '=', 'subjects.id')
            ->join('faculties', 'timetables.faculty_id', '=', 'faculties.id')
            ->join('rooms', 'timetables.classroom_id', '=', 'rooms.id')
            ->join('sections', 'timetables.section_id', '=', 'sections.id') // added section join
            ->join('week_days', 'timetables.day_id', '=', 'week_days.id');

        if ($type === 'faculty') {
            $query->where('timetables.faculty_id', $id);
        } elseif ($type === 'room') {
            $query->where('timetables.classroom_id', $id);
        } else {
            $query->where('timetables.section_id', $id);
        }

        $timetableData = $query->select(
                'timetables.*',
                'subjects.name as subject_name',
                'subjects.category as subject_category',
                'subjects.elective_group as elective_group',
                'subjects.short_name as subject_short_name',
                'subjects.subjectCode as subject_code',
                'subjects.theoryHoursPerWeek',
                'subjects.practicalHoursPerWeek',
                'subjects.tutorialHoursPerWeek',
                'faculties.name as faculty_name',
                'rooms.name as room_name',
                'rooms.type as room_type',
                'sections.name as section_name',
                'week_days.day_name'
            )
            ->orderBy('timetables.day_id')
            ->orderBy('timetables.slot_no')
            ->get();

        // Structure: day_id -> slot_no -> ARRAY of entries
        $structuredData   = [];
        $labOverflowSlots = []; // Track slots that are part of a lab session's 2nd hour

        foreach ($timetableData as $entry) {
            $dayId  = $entry->day_id;
            $slotNo = $entry->slot_no;

            if (!isset($structuredData[$dayId][$slotNo])) {
                $structuredData[$dayId][$slotNo] = [];
            }

            // Group entries. Avoid duplicates (e.g. if the same row is fetched)
            $exists = false;
            foreach ($structuredData[$dayId][$slotNo] as $existing) {
                if ($existing->id == $entry->id) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $structuredData[$dayId][$slotNo][] = $entry;
            }
        }

        // Logic to track overflow slots (2nd hour of lab)
        // Group by day, sort slots, then check for consecutive lab slots with same subject
        foreach ($structuredData as $dayId => $slots) {
            ksort($slots);
            $slotNos = array_keys($slots);
            foreach ($slotNos as $idx => $slotNo) {
                $entries = $slots[$slotNo];
                $hasLab = false;
                foreach($entries as $e) { if($e->type === 'lab') $hasLab = true; }
                if(!$hasLab) continue;

                $nextSlotNo = $slotNos[$idx + 1] ?? null;
                if (!$nextSlotNo) continue;

                $nextEntries = $slots[$nextSlotNo] ?? [];
                
                // Compare subjects between these two slots
                $currIds = collect($entries)->where('type', 'lab')->pluck('subject_id')->unique()->toArray();
                $nextIds = collect($nextEntries)->where('type', 'lab')->pluck('subject_id')->unique()->toArray();

                if (!empty(array_intersect($currIds, $nextIds))) {
                    $labOverflowSlots[$dayId][] = $nextSlotNo;
                }
            }
        }

        return response()->json([
            'timeSlots'         => $timeSlots,
            'weekDays'          => $weekDays,
            'timetableData'     => $structuredData,
            'labOverflowSlots'  => $labOverflowSlots,
        ]);
    }

    /**
     * Display timetable view for a specific section
     */
    public function show($sectionId)
    {
        // Get section details
        $section = DB::table('sections')
            ->join('departments', 'sections.departmentId', '=', 'departments.id')
            ->where('sections.id', $sectionId)
            ->select('sections.*', 'departments.name as department_name')
            ->first();

        if (!$section) {
            abort(404, 'Section not found');
        }

        // Fetch all time slots (including breaks)
        $timeSlots = DB::table('time_slots')
            ->orderBy('start_time')
            ->get();

        // Fetch all week days
        $weekDays = DB::table('week_days')
            ->orderBy('id')
            ->get();

        // Fetch timetable entries for this section
        $timetableData = DB::table('timetables')
            ->join('subjects', 'timetables.subject_id', '=', 'subjects.id')
            ->join('faculties', 'timetables.faculty_id', '=', 'faculties.id')
            ->join('rooms', 'timetables.classroom_id', '=', 'rooms.id')
            ->join('week_days', 'timetables.day_id', '=', 'week_days.id')
            ->where('timetables.section_id', $sectionId)
            ->select(
                'timetables.*',
                'subjects.name as subject_name',
                'subjects.category as subject_category',
                'subjects.elective_group as elective_group',
                'faculties.name as faculty_name',
                'rooms.name as room_name',
                'rooms.type as room_type',
                'week_days.day_name'
            )
            ->orderBy('timetables.day_id')
            ->orderBy('timetables.slot_no')
            ->get();

        // Structure: day_id -> slot_no -> [ entries ]
        // For lab slots that have multiple batches at the same slot_no,
        // we group them together so the view renders ONE merged cell.
        // We only keep the FIRST slot of each lab pair — the second slot
        // is tracked in $labOverflowSlots so the view can skip it.
        $structuredData    = [];  // day_id -> slot_no -> ['entries'=>[], 'is_lab'=>bool]
        $labOverflowSlots  = [];  // day_id -> [slot_no, ...] slots covered by rowspan

        foreach ($timetableData as $entry) {
            $dayId  = $entry->day_id;
            $slotNo = $entry->slot_no;

            if (!isset($structuredData[$dayId][$slotNo])) {
                $structuredData[$dayId][$slotNo] = [
                    'entries' => [],
                    'is_lab'  => $entry->type === 'lab',
                    'type'    => $entry->type,
                ];
            }

            // Avoid duplicate batches in the same slot cell
            $existing = collect($structuredData[$dayId][$slotNo]['entries']);
            $alreadyIn = $existing->contains(fn($e) =>
                $e->subject_id === $entry->subject_id
                && $e->batch     === $entry->batch
                && $e->slot_no   === $entry->slot_no
            );

            if (!$alreadyIn) {
                $structuredData[$dayId][$slotNo]['entries'][] = $entry;
            }
        }

        // For each lab slot pair, mark the 2nd slot as overflow so the view skips it
        // Lab slots come in consecutive pairs (s1, s2) with the same subject+batch
        foreach ($structuredData as $dayId => $slots) {
            ksort($slots);
            $slotNos = array_keys($slots);
            foreach ($slotNos as $idx => $slotNo) {
                $cell = $slots[$slotNo];
                if (!$cell['is_lab']) continue;

                // Find next slot number that exists
                $nextSlotNo = $slotNos[$idx + 1] ?? null;
                if (!$nextSlotNo) continue;

                $nextCell = $slots[$nextSlotNo] ?? null;
                if (!$nextCell || !$nextCell['is_lab']) continue;

                // Check if same subjects appear in both
                $thisSubjects = collect($cell['entries'])->pluck('subject_id')->unique()->toArray();
                $nextSubjects = collect($nextCell['entries'])->pluck('subject_id')->unique()->toArray();

                if (!empty(array_intersect($thisSubjects, $nextSubjects))) {
                    $labOverflowSlots[$dayId][] = $nextSlotNo;
                }
            }
        }

        return view('timetable.show', compact('section', 'timeSlots', 'weekDays', 'structuredData', 'labOverflowSlots'));
    }

    /**
     * Display timetable print view
     */
    public function print(\Illuminate\Http\Request $request, $id)
    {
        $type = $request->query('type', 'section');
        $target = null;

        if ($type === 'faculty') {
            $target = DB::table('faculties')->where('id', $id)->first();
            if ($target) {
                $target->semester = '-';
                $target->department_name = 'Allocation';
            }
        } elseif ($type === 'room') {
            $target = DB::table('rooms')->where('id', $id)->first();
            if ($target) {
                $target->semester = 'N/A';
                $target->department_name = 'Laboratory/Room';
            }
        } else {
            $target = DB::table('sections')
                ->join('departments', 'sections.departmentId', '=', 'departments.id')
                ->where('sections.id', $id)
                ->select('sections.id', 'sections.name', 'sections.semester', 'departments.name as department_name')
                ->first();
        }

        if (!$target) abort(404);

        return view('timetable.print', compact('target', 'type'));
    }
}