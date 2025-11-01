<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentMajorHistory extends Model
{
    use HasFactory;

    protected $table = 'student_major_history';

    protected $fillable = [
        'student_id',
        'major_id',
        'semester_id',
    ];

    protected $casts = [
        'student_id' => 'integer',
        'major_id' => 'integer',
        'semester_id' => 'integer',
    ];

    /**
     * Get the student that owns this history record.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the major for this history record.
     */
    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class);
    }

    /**
     * Get the semester for this history record.
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }
}
