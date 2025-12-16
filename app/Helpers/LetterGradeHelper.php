<?php

namespace App\Helpers;

class LetterGradeHelper
{
    /**
     * Calculate letter grade from numeric grade.
     *
     * Converts a numeric grade (0-100 scale) to a letter grade (A+ through F)
     * using the grading scale defined in config/grading.php.
     *
     * @param float|null $numericGrade Numeric grade (0-100)
     * @return string|null Letter grade (e.g., "A+", "B-", "F") or null if input is null
     */
    public static function calculate(?float $numericGrade): ?string
    {
        if ($numericGrade === null) {
            return null;
        }

        $scale = config('grading.letter_grade_scale');

        foreach ($scale as $threshold) {
            if ($numericGrade >= $threshold['min_grade']) {
                return $threshold['letter'];
            }
        }

        // Fallback for edge cases (should never reach if scale includes 0)
        return 'F';
    }
}
