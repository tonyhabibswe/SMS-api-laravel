<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradeableItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'max_points',
    ];

    protected $casts = [
        'max_points' => 'decimal:2',
    ];

    /**
     * Get the category that owns this item.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(GradeableCategory::class, 'category_id');
    }

    /**
     * Get all grades for this item.
     */
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class, 'gradeable_item_id');
    }
}
