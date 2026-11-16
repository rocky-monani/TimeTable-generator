<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Faculty;
use App\Models\FacultyAvailability;

class FacultyAvailabilityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // 🔹 SHOW PAGE
    public function index(Request $request)
    {
        $faculties = Faculty::all();

        $dayMapReverse = [
            1 => 'mon',
            2 => 'tue',
            3 => 'wed',
            4 => 'thu',
            5 => 'fri',
            6 => 'sat',
        ];

        $availability = [];

        if ($request->faculty_id) {

            $records = FacultyAvailability::where('faculty_id', $request->faculty_id)->get();

            foreach ($records as $r) {
                $day = $dayMapReverse[$r->day_id] ?? null;

                if ($day) {
                    // 0 = unavailable
                    $availability[$day][$r->slot_no] = 0;
                }
            }
        }

        return view('faculty.availability', compact('faculties', 'availability'));
    }

    // 🔹 TOGGLE LOGIC (INSERT / DELETE)
    public function toggleAvailability(Request $request)
    {
        $request->validate([
            'faculty_id' => 'required|exists:faculties,id',
            'day' => 'required',
            'slot' => 'required|integer',
        ]);

        $dayMap = [
            'mon' => 1,
            'tue' => 2,
            'wed' => 3,
            'thu' => 4,
            'fri' => 5,
            'sat' => 6,
        ];

        $day_id = $dayMap[$request->day];

        $existing = FacultyAvailability::where([
            'faculty_id' => $request->faculty_id,
            'day_id' => $day_id,
            'slot_no' => $request->slot
        ])->first();

        if ($existing) {
            // DELETE → becomes available
            $existing->delete();

            return response()->json([
                'status' => 'success',
                'state' => 'available'
            ]);
        } else {
            // INSERT → becomes unavailable
            FacultyAvailability::create([
                'faculty_id' => $request->faculty_id,
                'day_id' => $day_id,
                'slot_no' => $request->slot,
                'is_available' => 0
            ]);

            return response()->json([
                'status' => 'success',
                'state' => 'unavailable'
            ]);
        }
    }
}