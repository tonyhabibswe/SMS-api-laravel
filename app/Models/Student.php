<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'first_name',
        'father_name',
        'last_name',
        'major', // Keep legacy field for validation purposes
        'major_id', // New normalized field
        'email',
        'campus',
    ];

    protected $casts = [
        'major_id' => 'integer',
    ];

    /**
     * Get the major that this student belongs to.
     */
    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class);
    }

    /**
     * Get the major history for this student.
     */
    public function majorHistory(): HasMany
    {
        return $this->hasMany(StudentMajorHistory::class);
    }

    /**
     * Get the attendance records for this student.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the student's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->father_name} {$this->last_name}";
    }

    /**
     * Get the current major display name.
     * Uses the relationship if available, falls back to legacy field.
     */
    public function getCurrentMajorDisplayNameAttribute(): string
    {
        if ($this->major && $this->major->display_name) {
            return $this->major->display_name;
        }

        return $this->major ?? 'Unknown';
    }
}
