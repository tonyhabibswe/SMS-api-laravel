<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_session_id',
        'student_id',
        'value',
    ];

    // Define the relationship to Session
    public function courseSession()
    {
        return $this->belongsTo(CourseSession::class);
    }

    // Define the relationship to Student
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
