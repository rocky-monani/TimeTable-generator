<?php
use Illuminate\Support\Facades\DB;

$labs = DB::table('timetables')->where('type','lab')->whereNotNull('batch')->get()->groupBy('section_id');
foreach($labs as $sectionId => $entries) {
    $sName = DB::table('sections')->where('id', $sectionId)->value('name');
    $subjects = $entries->groupBy('subject_id');
    echo "Section: $sName (id:$sectionId) - ".count($subjects)." subjects\n";
    foreach($subjects as $subId => $rows) {
        $batches = $rows->pluck('batch')->unique()->sort()->values()->toArray();
        $subName = DB::table('subjects')->where('id', $subId)->value('name');
        echo "  Subj $subId ($subName): batches=".implode(',', $batches)."\n";
    }
    echo "\n";
}
