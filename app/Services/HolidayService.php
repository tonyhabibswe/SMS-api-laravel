<?php

namespace App\Services;

use App\DTOs\Holiday\HolidayCreateDTO;
use App\Repositories\CourseSessionRepository;
use App\Repositories\HolidayRepository;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class HolidayService
{
    protected HolidayRepository $holidayRepository;
    protected CourseSessionRepository $courseSessionRepository;
    
    public function __construct(HolidayRepository $holidayRepository, CourseSessionRepository $courseSessionRepository)
    {
        $this->holidayRepository = $holidayRepository;
        $this->courseSessionRepository = $courseSessionRepository;
    }
    
    /**
     * List all holidays for a given semester.
     *
     * @param int $semesterId
     * @return Collection
     */
    public function listHolidaysBySemesterId(int $semesterId): Collection
    {
        return $this->holidayRepository->getHolidaysBySemesterId($semesterId);
    }

    /**
     * Update a holiday's name and return the updated Holiday model.
     *
     * @param int $holidayId
     * @param string $name
     * @return \App\Models\Holiday
     * @throws Exception if the holiday is not found.
     */
    public function updateHolidayName(int $holidayId, ?string $name)
    {
        $holiday = $this->holidayRepository->updateHolidayName($holidayId, $name);

        if (!$holiday) {
            throw new Exception("Holiday not found", 404);
        }

        return $holiday;
    }

    /**
     * Create a holiday for a given semester and delete all course sessions that match the holiday date.
     *
     * @param int $semesterId
     * @param HolidayCreateDTO $dto
     * @return array An array containing the created holiday and the count of deleted sessions.
     * @throws Exception
     */
    public function createHoliday(int $semesterId, HolidayCreateDTO $dto): array
    {
        return DB::transaction(function () use ($semesterId, $dto) {
            // Create the holiday record.
            $holiday = $this->holidayRepository->createHoliday($semesterId, $dto->toArray());

            // Delete all course sessions in this semester that match the holiday date.
            // Note: The holiday date should be in a format that matches your session_start field, typically Y-m-d.
            $deletedCount = $this->courseSessionRepository->deleteSessionsBySemesterAndDate($semesterId, $dto->date);

            return [
                'holiday' => $holiday,
                'deletedSessions' => $deletedCount,
            ];
        });
    }
}
