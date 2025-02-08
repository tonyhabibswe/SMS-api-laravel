<?php

namespace App\Repositories;

use App\DTOs\Semester\SemesterCreateDTO;
use App\DTOs\Semester\SemesterEditDTO;
use App\Models\Semester;
use Illuminate\Support\Collection;

class SemesterRepository
{
    /**
     * Create a new Semester with optional holidays.
     *
     * @param SemesterCreateDTO $dto
     * @return Semester
     */
    public function createSemester(SemesterCreateDTO $dto)
    {
        // Create the Semester record.
        $semester = Semester::create([
            'name'       => $dto->name,
            'start_date' => $dto->start_date,
            'end_date'   => $dto->end_date,
        ]);

        // If holidays are provided, create related Holiday records.
        if ($dto->holidays && is_array($dto->holidays)) {
            foreach ($dto->holidays as $holidayDate) {
                $semester->holidays()->create([
                    'date' => $holidayDate,
                    'name' => null, // You can adjust this if you want to store a holiday name.
                ]);
            }
        }

        return $semester;
    }

    /**
     * Retrieve all semesters ordered by descending id.
     *
     * @return Collection
     */
    public function getAllSemesters(): Collection
    {
        return Semester::orderByDesc('id')->get();
    }

    /**
     * Update a semester record.
     *
     * @param SemesterEditDTO $dto
     * @return Semester|null
     */
    public function updateSemester(SemesterEditDTO $dto)
    {
        // Find the semester by its id.
        $semester = Semester::find($dto->id);
        if (!$semester) {
            return null;
        }
        
        // Update the semester's name.
        $semester->name = $dto->name;
        $semester->save();
        
        return $semester;
    }


    /**
     * Delete the semester with the given ID.
     *
     * @param int $id
     * @return bool True if deletion was successful, false if the semester was not found.
     */
    public function deleteSemester(int $id): bool
    {
        $semester = Semester::find($id);
        if (!$semester) {
            return false;
        }

        $semester->delete();
        return true;
    }
}
