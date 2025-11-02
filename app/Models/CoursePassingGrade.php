<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoursePassingGrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'major_id',
        'semester_id',
        'course_id',
        'grade_value',
    ];

    protected $casts = [
        'major_id' => 'integer',
        'semester_id' => 'integer',
        'course_id' => 'integer',
        'grade_value' => 'decimal:2',
    ];

    /**
     * Get the major that this passing grade belongs to.
     */
    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class);
    }

    /**
     * Get the semester that this passing grade belongs to.
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Get the course that this passing grade belongs to.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
