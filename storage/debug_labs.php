<?php
use App\Services\TimetableGeneratorService;
use Illuminate\Support\Facades\DB;

$service = new TimetableGeneratorService();
$service->generate();
$labs = DB::table('timetables')->where('type', 'lab')->get();

foreach($labs as $l) {
    echo "ID: {$l->id} | Day: {$l->day_id} | Slot: {$l->slot_no} | Subj: {$l->subject_id} | Fac: {$l->faculty_id} | Batch: {$l->batch}\n";
}
