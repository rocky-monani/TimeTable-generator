<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use Yajra\DataTables\Facades\DataTables;

class RoomController extends Controller
{
    public function index()
    {
        return view('rooms.index');
    }

    public function list()
    {
        $data = Room::query();

        return DataTables::of($data)

            ->addColumn('type_badge', function ($row) {

                return $row->type == 'lab'
                    ? '<span class="badge badge-info">Lab</span>'
                    : '<span class="badge badge-success">Classroom</span>';
            })

            ->addColumn('action', function ($row) {

                return '
                    <button class="btn btn-sm btn-primary editBtn" data-id="'.$row->id.'"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-danger deleteBtn" data-id="'.$row->id.'"><i class="fas fa-trash"></i></button>
                ';
            })

            ->rawColumns(['action', 'type_badge'])
            ->make(true);
    }

    public function store(Request $request)
    {
        // ✅ VALIDATION
        if (!$request->name || !$request->type) {
            return response()->json(['error' => 'Name and type required']);
        }

        if (!in_array($request->type, ['classroom', 'lab'])) {
            return response()->json(['error' => 'Invalid type']);
        }

        Room::updateOrCreate(
            ['id' => $request->id],
            [
                'name' => $request->name,
                'type' => $request->type,
                'capacity' => $request->capacity ?? 0
            ]
        );

        return response()->json(['success' => true]);
    }

    public function edit($id)
    {
        return Room::find($id);
    }

    public function delete($id)
    {
        Room::where('id', $id)->delete();

        return response()->json(['success' => true]);
    }
}