<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = ['semester_id', 'date', 'name'];

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
}
