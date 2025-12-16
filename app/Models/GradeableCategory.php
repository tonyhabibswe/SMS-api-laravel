<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradeableCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_section_id',
        'name',
        'weight_percent',
        'algorithm',
    ];

    protected $casts = [
        'weight_percent' => 'decimal:2',
        'algorithm' => 'string',
    ];

    /**
     * Get the course section that owns this category.
     */
    public function courseSection(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class);
    }

    /**
     * Get all gradeable items for this category.
     */
    public function gradeableItems(): HasMany
    {
        return $this->hasMany(GradeableItem::class, 'category_id');
    }
}
