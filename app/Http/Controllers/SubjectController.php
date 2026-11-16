<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\Department;
use App\Models\Faculty;
use Yajra\DataTables\Facades\DataTables;

class SubjectController extends Controller
{
    public function index()
    {
        $departments = Department::all();
        $faculties = Faculty::all();

        return view('subjects.index', compact('departments', 'faculties'));
    }

    public function list(Request $request)
    {
        if ($request->ajax()) {

            $data = Subject::with('department')
                ->select(
                    'id',
                    'name',
                    'subjectCode',
                    'semester',
                    'departmentId',
                    'faculty_id',
                    'faculty_id_2',
                    'theoryHoursPerWeek',
                    'practicalHoursPerWeek',
                    'category',
                    'elective_group'
                );

            return DataTables::of($data)

                ->addColumn('department', function ($row) {
                    return $row->department->name ?? '';
                })

                ->addColumn('action', function ($row) {
                    return '
                        <button class="btn btn-sm btn-primary editBtn" data-id="'.$row->id.'"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-danger deleteBtn" data-id="'.$row->id.'"><i class="fas fa-trash"></i></button>
                    ';
                })

                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function store(Request $request)
    {
        Subject::create([
            'name'                  => $request->name,
            'subjectCode'           => $request->subjectCode,
            'semester'              => $request->semester,
            'departmentId'          => $request->departmentId ?: null,
            'faculty_id'            => $request->faculty_id ?: null,
            'faculty_id_2'          => $request->faculty_id_2 ?: null,
            'theoryHoursPerWeek'    => (int) ($request->theoryHoursPerWeek ?? 0),
            'practicalHoursPerWeek' => (int) ($request->practicalHoursPerWeek ?? 0),
            'category'              => $request->category,
            'elective_group'        => $request->elective_group
        ]);

        return response()->json(['success' => true]);
    }

    public function edit($id)
    {
        return Subject::find($id);
    }

    public function update(Request $request, $id)
    {
        Subject::where('id', $id)->update([
            'name'                  => $request->name,
            'subjectCode'           => $request->subjectCode,
            'semester'              => $request->semester,
            'departmentId'          => $request->departmentId ?: null,
            'faculty_id'            => $request->faculty_id ?: null,
            'faculty_id_2'          => $request->faculty_id_2 ?: null,
            'theoryHoursPerWeek'    => (int) ($request->theoryHoursPerWeek ?? 0),
            'practicalHoursPerWeek' => (int) ($request->practicalHoursPerWeek ?? 0),
            'category'              => $request->category,
            'elective_group'        => $request->elective_group
        ]);

        return response()->json(['success' => true]);
    }
    public function destroy($id)
    {
        Subject::where('id', $id)->delete();

        return response()->json(['success' => true]);
    }
}