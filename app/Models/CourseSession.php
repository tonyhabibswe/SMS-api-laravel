<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_section_id',
        'session_start',
        'session_end',
        'room',
    ];

    protected $dates = [
        'session_start',
        'session_end',
    ];

    // Define the relationship to CourseSection
    public function courseSection()
    {
        return $this->belongsTo(CourseSection::class);
    }
}