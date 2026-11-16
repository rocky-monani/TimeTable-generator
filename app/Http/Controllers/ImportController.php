<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SmartCollegeImport;
use App\Exports\TemplateExport;

class ImportController extends Controller
{
    public function index()
    {
        return view('import.index');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {

            DB::beginTransaction();

            Excel::import(new SmartCollegeImport, $request->file('file'));

            DB::commit();

            return back()->with('success', '✅ Excel Imported Successfully');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', '❌ Import Failed: '.$e->getMessage());
        }
    }

    public function template()
    {
        return Excel::download(new TemplateExport, 'smart_college_template.xlsx');
    }

    public function pasteView()
    {
        $departments = DB::table('departments')->get();
        $faculties = DB::table('faculties')->orderBy('name')->get();
        return view('import.paste', compact('departments', 'faculties'));
    }

    public function processPaste(Request $request)
    {
        $subjects = $request->input('subjects');
        $departmentId = $request->input('department_id');
        
        if (empty($subjects) || !is_array($subjects)) {
            return response()->json(['success' => false, 'message' => 'No valid subjects provided.']);
        }

        DB::beginTransaction();
        try {
            foreach ($subjects as $sub) {
                // Generate short name logic if empty
                $shortName = '';
                if (!empty($sub['name'])) {
                    // Extract first letters
                    preg_match_all('/\b([a-zA-Z])/i', $sub['name'], $matches);
                    $shortName = strtoupper(implode('', $matches[1]));
                    if (strlen($shortName) > 6) {
                        $shortName = substr($shortName, 0, 6);
                    }
                }

                DB::table('subjects')->updateOrInsert(
                    [
                        'subjectCode' => $sub['subjectCode'],
                        'departmentId' => $departmentId
                    ],
                    [
                        'name' => $sub['name'],
                        'short_name' => $shortName,
                        'category' => $sub['category'] ?? 'Core',
                        'elective_group' => $sub['elective_group'] ?? null,
                        'semester' => $sub['semester'] ?? 1,
                        'theoryHoursPerWeek' => (int) ($sub['theory'] ?? 0),
                        'tutorialHoursPerWeek' => (int) ($sub['tutorial'] ?? 0),
                        'practicalHoursPerWeek' => (int) ($sub['practical'] ?? 0),
                        'faculty_id' => !empty($sub['faculty_id']) ? $sub['faculty_id'] : null,
                        'faculty_id_2' => !empty($sub['faculty_id_2']) ? $sub['faculty_id_2'] : null,
                        'updated_at' => now(),
                    ]
                );
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Subjects imported successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}