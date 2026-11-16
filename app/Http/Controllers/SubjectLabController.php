<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\Room;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SubjectLabController extends Controller
{
    public function index()
    {
        return view('subject_labs.index');
    }

    public function list()
    {
        $labs = \App\Models\Room::where('type', 'lab')->get();

        return \Yajra\DataTables\Facades\DataTables::of(\App\Models\Subject::with('labs'))

            ->addColumn('subject', fn($row) => $row->name)

            ->addColumn('labs', function ($row) use ($labs) {

                $html = "<select class='form-control select2 labSelect_{$row->id}' data-placeholder='Select labs...' multiple style='width: 100%;'>";
                foreach ($labs as $lab) {
                    $selected = $row->labs->contains('id', $lab->id) ? 'selected' : '';
                    $html .= "<option value='{$lab->id}' {$selected}>{$lab->name}</option>";
                }
                $html .= "</select>";
                return $html;
            })

            ->addColumn(
                'action',
                fn($row) =>
                '<button class="btn btn-primary saveBtn" data-id="' . $row->id . '">Save</button>'
            )

            ->rawColumns(['labs', 'action'])
            ->make(true);
    }

    public function save(Request $request)
    {
        // 🔥 VALIDATION
        if (!$request->subject_id) {
            return response()->json(['error' => 'Invalid subject']);
        }

        DB::table('subject_labs')
            ->where('subject_id', $request->subject_id)
            ->delete();

        if (!empty($request->labs)) {

            $insertData = [];

            foreach ($request->labs as $lab) {

                $insertData[] = [
                    'subject_id' => $request->subject_id,
                    'room_id' => $lab
                ];
            }

            DB::table('subject_labs')->insert($insertData);
        }

        return response()->json(['success' => true]);
    }
}