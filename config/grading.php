<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Letter Grade Scale
    |--------------------------------------------------------------------------
    |
    | This configuration defines the mapping between numeric grades (0-100)
    | and letter grades. The scale is ordered from highest to lowest.
    | Each entry specifies the minimum numeric grade required for that letter.
    |
    | Default scale:
    | - A+: 97-100
    | - A:  93-96.99
    | - A-: 89-92.99
    | - B+: 85-88.99
    | - B:  80-84.99
    | - B-: 77-79.99
    | - C+: 73-76.99
    | - C:  70-72.99
    | - C-: 66-69.99
    | - D+: 63-65.99
    | - D:  60-62.99
    | - F:  0-59.99
    |
    | The calculation iterates through the array from top to bottom and returns
    | the first letter grade where the numeric grade meets the minimum threshold.
    |
    */

    'letter_grade_scale' => [
        ['letter' => 'A+', 'min_grade' => 97],
        ['letter' => 'A',  'min_grade' => 93],
        ['letter' => 'A-', 'min_grade' => 89],
        ['letter' => 'B+', 'min_grade' => 85],
        ['letter' => 'B',  'min_grade' => 80],
        ['letter' => 'B-', 'min_grade' => 77],
        ['letter' => 'C+', 'min_grade' => 73],
        ['letter' => 'C',  'min_grade' => 70],
        ['letter' => 'C-', 'min_grade' => 66],
        ['letter' => 'D+', 'min_grade' => 63],
        ['letter' => 'D',  'min_grade' => 60],
        ['letter' => 'F',  'min_grade' => 0],
    ],
];
