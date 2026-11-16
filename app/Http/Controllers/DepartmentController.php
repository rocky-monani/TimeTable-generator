<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use Yajra\DataTables\Facades\DataTables;

class DepartmentController extends Controller
{
    public function index()
    {
        return view('departments.index');
    }

    public function list(Request $request)
    {
        if ($request->ajax()) {

            $data = Department::select('id', 'name')->orderBy('id');

            return DataTables::of($data)

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
        if (!$request->name) {
            return response()->json(['error' => 'Name required']);
        }

        Department::create([
            'name' => $request->name
        ]);

        return response()->json(['success' => true]);
    }

    public function edit($id)
    {
        return Department::find($id);
    }

    public function update(Request $request, $id)
    {
        Department::where('id', $id)->update([
            'name' => $request->name
        ]);

        return response()->json(['success' => true]);
    }

    public function delete($id) // ✅ FIXED NAME
    {
        Department::where('id', $id)->delete();

        return response()->json(['success' => true]);
    }
}