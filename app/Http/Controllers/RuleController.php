<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rule;
use Yajra\DataTables\Facades\DataTables;

class RuleController extends Controller
{

    public function index()
    {
        return view('rules.index');
    }


    public function list()
    {
        $data = Rule::query();

        return DataTables::of($data)

        ->addColumn('action',function($row){

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

        Rule::updateOrCreate(

            ['id'=>$request->id],

            [
                'rule_key'=>$request->rule_key,
                'rule_value'=>$request->rule_value
            ]

        );

        return response()->json(['success'=>true]);
    }


    public function edit($id)
    {
        return Rule::find($id);
    }


    public function delete($id)
    {
        Rule::find($id)->delete();
        return response()->json(['success'=>true]);
    }

}
