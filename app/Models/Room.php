<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'name',
        'type',
        'capacity'
    ];

    // 🔥 scope for filtering
    public function scopeClassrooms($query)
    {
        return $query->where('type', 'classroom');
    }

    public function scopeLabs($query)
    {
        return $query->where('type', 'lab');
    }
}