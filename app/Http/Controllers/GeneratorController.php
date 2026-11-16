<?php

namespace App\Http\Controllers;

use App\Services\TimetableGeneratorService;

use Illuminate\Http\Request;

class GeneratorController extends Controller
{
    public function generate(Request $request)
    {
        $sectionId = $request->query('section_id');
        
        $service = new TimetableGeneratorService();

        // ── PRE-GENERATION VALIDATION ──
        $errors = $service->validate($sectionId);
        if (!empty($errors)) {
            return redirect('/timetable')->with('generation_errors', $errors);
        }

        // ── GENERATE ──
        $warnings = $service->generate($sectionId);

        $msg = $sectionId ? 'Section Timetable Generated Successfully' : 'All Timetables Generated Successfully';

        if (!empty($warnings)) {
            // Generation succeeded but some slots could not be filled — flash both
            return redirect('/timetable')
                ->with('success', $msg)
                ->with('generation_errors', $warnings);
        }

        return redirect('/timetable')->with('success', $msg);
    }
}