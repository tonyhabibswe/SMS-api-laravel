<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'gradeable_item_id',
        'course_enrollment_id',
        'grade_value',
    ];

    protected $casts = [
        'grade_value' => 'decimal:2',
    ];

    /**
     * Get the gradeable item that owns this grade.
     */
    public function gradeableItem(): BelongsTo
    {
        return $this->belongsTo(GradeableItem::class, 'gradeable_item_id');
    }

    /**
     * Get the course enrollment that owns this grade.
     */
    public function courseEnrollment(): BelongsTo
    {
        return $this->belongsTo(CourseEnrollment::class, 'course_enrollment_id');
    }

    /**
     * Calculate percentage based on grade_value and max_points.
     */
    protected function percentage(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->grade_value || !$this->gradeableItem) {
                    return null;
                }

                $maxPoints = $this->gradeableItem->max_points;
                if ($maxPoints <= 0) {
                    return null;
                }

                return round(($this->grade_value / $maxPoints) * 100, 2);
            }
        );
    }
}
