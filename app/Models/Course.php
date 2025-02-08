<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
    ];

    // Define the relationship to Sections
    public function sections()
    {
        return $this->hasMany(CourseSection::class);
    }
}