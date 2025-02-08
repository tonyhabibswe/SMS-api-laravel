<?php

if (!function_exists('getDayAbbreviation')) {
    function getDayAbbreviation(string $day): string
    {
        $dayAbbreviations = [
            'monday' => 'M',
            'tuesday' => 'T',
            'wednesday' => 'W',
            'thursday' => 'TH',
            'friday' => 'F',
            'saturday' => 'SA',
            'sunday' => 'SU',
        ];

        // Convert the input day to lowercase and fetch the abbreviation
        $day = strtolower($day);

        return $dayAbbreviations[$day] ?? throw new InvalidArgumentException("Invalid day of week: $day");
    }
}