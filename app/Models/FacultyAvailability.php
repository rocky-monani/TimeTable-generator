<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacultyAvailability extends Model
{
    protected $table = 'faculty_availabilities';

    protected $fillable = [
        'faculty_id',
        'day_id',
        'slot_no',
        'is_available'
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }
}