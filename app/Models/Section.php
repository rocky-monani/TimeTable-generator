<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = [
        'name',
        'semester',
        'departmentId'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'departmentId');
    }
}