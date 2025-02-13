<?php

namespace App\Repositories;

use App\Models\Holiday;
use Illuminate\Support\Collection;

class HolidayRepository
{
    /**
     * Retrieve all holidays for a given semester ID, ordered by date.
     *
     * @param int $semesterId
     * @return Collection
     */
    public function getHolidaysBySemesterId(int $semesterId): Collection
    {
        return Holiday::where('semester_id', $semesterId)
            ->orderBy('date', 'asc')
            ->get();
    }

    /**
     * Update the name of a holiday by its ID.
     *
     * @param int $holidayId
     * @param string $name
     * @return Holiday|null
     */
    public function updateHolidayName(int $holidayId, ?string $name): ?Holiday
    {
        $holiday = Holiday::find($holidayId);
        if (!$holiday) {
            return null;
        }

        $holiday->name = $name;
        $holiday->save();

        return $holiday;
    }

    /**
     * Create a new holiday record for a given semester.
     *
     * @param int $semesterId
     * @param array $data
     * @return Holiday
     */
    public function createHoliday(int $semesterId, array $data): Holiday
    {
        // Assumes Holiday model has semester_id, date, and name.
        return Holiday::create(array_merge(['semester_id' => $semesterId], $data));
    }

}
