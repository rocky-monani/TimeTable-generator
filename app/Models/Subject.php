<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $table = 'subjects';

    protected $fillable = [
        'name',
        'subjectCode',
        'semester',
        'departmentId',
        'faculty_id',
        'faculty_id_2',
        'theoryHoursPerWeek',
        'practicalHoursPerWeek',
        'category',
        'elective_group'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'departmentId');
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class, 'faculty_id');
    }

    public function faculty2()
    {
        return $this->belongsTo(Faculty::class, 'faculty_id_2');
    }

    public function labs()
    {
        return $this->belongsToMany(
            \App\Models\Room::class,
            'subject_labs',
            'subject_id',
            'room_id'
        );
    }
}