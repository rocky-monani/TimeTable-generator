<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Section;
use App\Models\Room;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SectionRoomController extends Controller
{
    public function index()
    {
        return view('section_rooms.index');
    }

    public function list()
    {
        $rooms = \App\Models\Room::where('type', 'classroom')->get();
        $mapped = \DB::table('section_rooms')->pluck('room_id', 'section_id');

        return \Yajra\DataTables\Facades\DataTables::of(\App\Models\Section::query())

            ->addColumn('section', fn($row) => $row->name)

            ->addColumn('room', function ($row) use ($rooms, $mapped) {

                $html = '<select id="room_' . $row->id . '" class="form-control">';

                foreach ($rooms as $room) {

                    $selected = ($mapped[$row->id] ?? null) == $room->id ? 'selected' : '';

                    $html .= "<option value='{$room->id}' $selected>{$room->name}</option>";
                }

                $html .= '</select>';

                return $html;
            })

            ->addColumn(
                'action',
                fn($row) =>
                '<button class="btn btn-success saveRoom" data-id="' . $row->id . '">Save</button>'
            )

            ->rawColumns(['room', 'action'])
            ->make(true);
    }

    public function save(Request $request)
    {
        // 🔥 VALIDATION
        if (!$request->section_id || !$request->room_id) {
            return response()->json(['error' => 'Invalid data']);
        }

        // 🔥 CHECK ROOM TYPE
        $room = Room::find($request->room_id);

        if (!$room || $room->type !== 'classroom') {
            return response()->json(['error' => 'Only classroom allowed']);
        }

        DB::table('section_rooms')
            ->updateOrInsert(
                ['section_id' => $request->section_id],
                ['room_id' => $request->room_id]
            );

        return response()->json(['success' => true]);
    }
}