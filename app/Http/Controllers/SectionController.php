<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Section;
use App\Models\Department;
use Yajra\DataTables\Facades\DataTables;

class SectionController extends Controller
{
    public function index()
    {
        $departments = Department::all();
        return view('sections.index', compact('departments'));
    }

    public function list(Request $request)
    {
        if ($request->ajax()) {

            $data = Section::with('department')
                ->select('id', 'name', 'semester', 'departmentId', 'batches');

            return DataTables::of($data)

                ->addColumn('department', function ($row) {
                    return $row->department->name ?? '';
                })

                ->addColumn('action', function ($row) {
                    return '
                        <button class="btn btn-primary btn-sm editBtn" data-id="' . $row->id . '"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-danger btn-sm deleteBtn" data-id="' . $row->id . '"><i class="fas fa-trash"></i></button>
                    ';
                })

                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function store(Request $request)
    {
        // ✅ VALIDATION
        if (!$request->name || !$request->semester || !$request->departmentId) {
            return response()->json(['error' => 'All fields required']);
        }

        Section::create([
            'name' => $request->name,
            'semester' => $request->semester,
            'departmentId' => $request->departmentId,
            'batches' => $request->batches ?? 1
        ]);

        return response()->json(['success' => true]);
    }

    public function edit($id)
    {
        return Section::find($id);
    }

    public function update(Request $request, $id)
    {
        Section::where('id', $id)->update([
            'name' => $request->name,
            'semester' => $request->semester,
            'departmentId' => $request->departmentId,
            'batches' => $request->batches ?? 1
        ]);

        return response()->json(['success' => true]);
    }

    public function delete($id) // ✅ FIXED NAME
    {
        Section::where('id', $id)->delete();

        return response()->json(['success' => true]);
    }
}