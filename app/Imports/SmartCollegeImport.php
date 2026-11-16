<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

use App\Models\Department;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Faculty;
use App\Models\Room;

class SmartCollegeImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {

            if(empty($row[0])) continue;

            $data = explode('|', $row[0]);
            $module = strtolower(trim($data[0]));

            try {

                if($module == 'department'){
                    Department::updateOrCreate(
                        ['name' => trim($data[1])],
                        ['code' => $data[2] ?? null]
                    );
                }

                if($module == 'section'){
                    Section::updateOrCreate(
                        [
                            'name' => trim($data[1]),
                            'departmentId' => trim($data[2])
                        ],
                        [
                            'semester' => trim($data[3] ?? 1)
                        ]
                    );
                }

                if($module == 'subject'){
                    Subject::updateOrCreate(
                        ['subjectCode' => trim($data[2])],
                        [
                            'name' => trim($data[1]),
                            'departmentId' => trim($data[3]),
                            'theoryHoursPerWeek' => trim($data[4]),
                            'practicalHoursPerWeek' => trim($data[5]),
                            'category' => trim($data[6])
                        ]
                    );
                }

                if($module == 'faculty'){
                    Faculty::updateOrCreate(
                        ['name' => trim($data[1])],
                        [
                            'maxDailyHours' => trim($data[2]),
                            'weeklyLoad' => trim($data[3]),
                            'subjectId' => trim($data[4])
                        ]
                    );
                }

                if($module == 'room'){
                    Room::updateOrCreate(
                        ['name' => trim($data[1])],
                        [
                            'type' => trim($data[2]),
                            'capacity' => trim($data[3])
                        ]
                    );
                }

            } catch (\Exception $e) {
                \Log::error('Import error: '.$e->getMessage());
            }
        }
    }
}