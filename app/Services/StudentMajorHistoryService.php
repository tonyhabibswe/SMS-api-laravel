<?php

namespace App\Services;

use App\DTOs\Student\StudentMajorHistoryDTO;
use App\Models\Student;
use App\Repositories\StudentMajorHistoryRepository;
use App\Services\MajorService;
use Illuminate\Support\Collection;

class StudentMajorHistoryService
{
    protected StudentMajorHistoryRepository $historyRepository;
    protected MajorService $majorService;

    public function __construct(
        StudentMajorHistoryRepository $historyRepository,
        MajorService $majorService
    ) {
        $this->historyRepository = $historyRepository;
        $this->majorService = $majorService;
    }

    /**
     * Track major change for a student.
     *
     * @param int $studentId
     * @param string $newMajorSystemName
     * @param int $semesterId
     * @return bool True if history was created (major changed), false if no change
     */
    public function trackMajorChange(int $studentId, string $newMajorSystemName, int $semesterId): bool
    {
        // Find or create the major
        $major = $this->majorService->findOrCreateMajor($newMajorSystemName);

        // Check if this exact combination already exists
        if ($this->historyRepository->historyExists($studentId, $major->id, $semesterId)) {
            return false; // No change needed
        }

        // Get the latest major for this student in this semester
        $latestHistory = $this->historyRepository->getLatestMajorForStudentInSemester($studentId, $semesterId);

        // If the major is different from the latest, create a new history record
        if (!$latestHistory || $latestHistory->major_id !== $major->id) {
            $this->historyRepository->createHistory($studentId, $major->id, $semesterId);
            return true; // Major changed
        }

        return false; // No change
    }

    /**
     * Get student major history as DTOs.
     *
     * @param int $studentId
     * @return Collection|StudentMajorHistoryDTO[]
     */
    public function getStudentMajorHistory(int $studentId): Collection
    {
        $history = $this->historyRepository->getHistoryByStudentId($studentId);

        return $history->map(function ($historyRecord) {
            return StudentMajorHistoryDTO::fromModel($historyRecord);
        });
    }

    /**
     * Get current major for a student.
     *
     * @param int $studentId
     * @return StudentMajorHistoryDTO|null
     */
    public function getCurrentStudentMajor(int $studentId): ?StudentMajorHistoryDTO
    {
        $latestHistory = $this->historyRepository->getLatestMajorForStudent($studentId);

        return $latestHistory ? StudentMajorHistoryDTO::fromModel($latestHistory) : null;
    }

    /**
     * Create initial major history for existing student.
     *
     * @param int $studentId
     * @param int $majorId
     * @param int $semesterId
     * @return void
     */
    public function createInitialHistory(int $studentId, int $majorId, int $semesterId): void
    {
        if (!$this->historyRepository->historyExists($studentId, $majorId, $semesterId)) {
            $this->historyRepository->createHistory($studentId, $majorId, $semesterId);
        }
    }

    /**
     * Get major history for a semester.
     *
     * @param int $semesterId
     * @return Collection|StudentMajorHistoryDTO[]
     */
    public function getMajorHistoryBySemester(int $semesterId): Collection
    {
        $history = $this->historyRepository->getMajorHistoryBySemester($semesterId);

        return $history->map(function ($historyRecord) {
            return StudentMajorHistoryDTO::fromModel($historyRecord);
        });
    }
}
