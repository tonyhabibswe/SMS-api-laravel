<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_session_id',
        'course_enrollment_id',
        'value',
    ];

    protected $casts = [
        'course_session_id' => 'integer',
        'course_enrollment_id' => 'integer',
    ];

    /**
     * Get the course session for this attendance.
     */
    public function courseSession(): BelongsTo
    {
        return $this->belongsTo(CourseSession::class);
    }

    /**
     * Get the course enrollment for this attendance.
     */
    public function courseEnrollment(): BelongsTo
    {
        return $this->belongsTo(CourseEnrollment::class);
    }

    /**
     * Get the student through enrollment relationship.
     */
    public function student(): BelongsTo
    {
        return $this->courseEnrollment->student();
    }
}
