<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Faculty;
use Yajra\DataTables\Facades\DataTables;

class FacultyController extends Controller
{
    public function index()
    {
        return view('faculty.index');
    }

    public function list(Request $request)
    {
        $data = Faculty::query();

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

    public function store(Request $request)
    {
        Faculty::updateOrCreate(
            ['id' => $request->id],
            [
                'name' => $request->name,
                'maxDailyHours' => $request->maxDailyHours,
                'weeklyLoad' => $request->weeklyLoad
            ]
        );

        return response()->json(['success' => true]);
    }

    public function edit($id)
    {
        return Faculty::find($id);
    }

    public function delete($id)
    {
        Faculty::find($id)->delete();
        return response()->json(['success' => true]);
    }
}