<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'semester_id',
        'section_code',
        'time',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function sessions()
    {
        return $this->hasMany(CourseSession::class);
    }
    
    // Define a relationship to retrieve the first session only
    public function firstSession()
    {
        return $this->hasOne(CourseSession::class)->oldestOfMany();
    }
}
