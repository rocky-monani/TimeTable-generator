<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    protected $table = 'faculties';

    protected $fillable = [
        'name',
        'maxDailyHours',
        'weeklyLoad'
    ];

    // 🔥 Faculty has many subjects
    public function subjects()
    {
        return $this->hasMany(Subject::class, 'faculty_id');
    }
}