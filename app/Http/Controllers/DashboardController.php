<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Department;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Faculty;
use App\Models\Room;
use App\Models\Rule;

class DashboardController extends Controller
{

    public function index()
    {

        // Basic counts
        $departments = Department::count();
        $sections = Section::count();
        $subjects = Subject::count();
        $faculty = Faculty::count();

        $rooms = Room::where('type', 'classroom')->count();
        $labs = Room::where('type', 'lab')->count();

        $rules = Rule::count();


        /*
        |--------------------------------------------------------------------------
        | Data Health Checks
        |--------------------------------------------------------------------------
        */

        // sections without room
        $sectionsWithoutRoom = DB::table('sections')
            ->leftJoin('section_rooms', 'sections.id', '=', 'section_rooms.section_id')
            ->whereNull('section_rooms.room_id')
            ->count();


        // subjects without lab (Excluding synchronized subjects)
        $subjectsWithoutLab = DB::table('subjects')
            ->leftJoin('subject_labs', 'subjects.id', '=', 'subject_labs.subject_id')
            ->where('subjects.practicalHoursPerWeek', '>', 0) // ✅ ONLY practical subjects
            ->whereNotIn('subjects.name', ['Creative Break', 'L&D', 'creative breake'])
            ->whereNull('subject_labs.room_id')
            ->count();


        // subjects without faculty (Excluding synchronized subjects)
        $subjectsWithoutFaculty = DB::table('subjects')
            ->whereNull('faculty_id')
            ->whereNotIn('subjects.name', ['Creative Break', 'L&D', 'creative breake'])
            ->count();


        // faculty availability missing
        $facultyWithoutAvailability = DB::table('faculties')
            ->leftJoin('faculty_availabilities', 'faculties.id', '=', 'faculty_availabilities.faculty_id')
            ->whereNull('faculty_availabilities.faculty_id')
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Timetable Readiness
        |--------------------------------------------------------------------------
        */

        $checks = [

            $sectionsWithoutRoom == 0,
            $subjectsWithoutLab == 0,
            $subjectsWithoutFaculty == 0,
            $facultyWithoutAvailability == 0

        ];

        $readyCount = collect($checks)->filter()->count();

        $readiness = ($readyCount / count($checks)) * 100;



        /*
        |--------------------------------------------------------------------------
        | Chart Data (Sections per Department)
        |--------------------------------------------------------------------------
        */

        $chartLabels = Department::pluck('name');

        $chartData = Department::withCount('sections')
            ->pluck('sections_count');



        /*
        |--------------------------------------------------------------------------
        | Recent Data
        |--------------------------------------------------------------------------
        */

        $recentSubjects = Subject::latest()
            ->limit(5)
            ->get();

        $recentFaculty = Faculty::latest()
            ->limit(5)
            ->get();



        return view('dashboard', compact(

            'departments',
            'sections',
            'subjects',
            'faculty',
            'rooms',
            'labs',
            'rules',

            'sectionsWithoutRoom',
            'subjectsWithoutLab',
            'subjectsWithoutFaculty',
            'facultyWithoutAvailability',

            'readiness',

            'chartLabels',
            'chartData',

            'recentSubjects',
            'recentFaculty'

        ));

    }

}