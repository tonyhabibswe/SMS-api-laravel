<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentCourseStatus extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'label',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /**
     * Get all course enrollments with this status.
     */
    public function courseEnrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class, 'status_id');
    }
}
