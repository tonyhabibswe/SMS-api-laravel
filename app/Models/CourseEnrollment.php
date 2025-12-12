<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_section_id',
        'student_id',
        'major_id',
        'status_id',
        'final_grade',
        'letter_grade',
    ];

    protected $casts = [
        'course_section_id' => 'integer',
        'student_id' => 'integer',
        'major_id' => 'integer',
        'status_id' => 'integer',
        'final_grade' => 'integer',
    ];

    protected $with = ['status'];

    /**
     * Get the course section for this enrollment.
     */
    public function courseSection(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class);
    }

    /**
     * Get the status for this enrollment.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(StudentCourseStatus::class, 'status_id');
    }

    /**
     * Get the student for this enrollment.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the major for this enrollment.
     */
    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class);
    }

    /**
     * Get the attendance records for this enrollment.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
