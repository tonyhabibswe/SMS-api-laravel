<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Major extends Model
{
    use HasFactory;

    protected $fillable = [
        'system_name',
        'label',
    ];

    /**
     * Get the students that belong to this major.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Get the major history records for this major.
     */
    public function majorHistory(): HasMany
    {
        return $this->hasMany(StudentMajorHistory::class);
    }

    /**
     * Get the display name for the major.
     * Returns label if available, otherwise system_name.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->label ?? $this->system_name;
    }
}
