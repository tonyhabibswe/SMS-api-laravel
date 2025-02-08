<?php

namespace App\Services;

use App\DTOs\Semester\SemesterCreateDTO;
use App\DTOs\Semester\SemesterEditDTO;
use App\DTOs\Semester\SemesterListDTO;
use App\Repositories\SemesterRepository;

class SemesterService
{
    protected SemesterRepository $semesterRepository;

    public function __construct(SemesterRepository $semesterRepository)
    {
        $this->semesterRepository = $semesterRepository;
    }

    /**
     * Create a new semester and return its DTO.
     *
     * @param SemesterCreateDTO $dto
     * @return SemesterListDTO
     */
    public function createSemester(SemesterCreateDTO $dto): SemesterListDTO
    {
        $semester = $this->semesterRepository->createSemester($dto);
        // Load the holidays relationship to include in the DTO.
        $semester->load('holidays');
        return SemesterListDTO::fromModel($semester);
    }

    /**
     * List all semesters as DTOs.
     *
     * @return \Illuminate\Support\Collection|SemesterListDTO[]
     */
    public function listSemesters()
    {
        $semesters = $this->semesterRepository->getAllSemesters();
        return $semesters->map(function ($semester) {
            return SemesterListDTO::fromModel($semester);
        });
    }


    /**
     * Update a semester and return its updated DTO.
     *
     * @param SemesterEditDTO $dto
     * @return SemesterListDTO|null
     */
    public function updateSemester(SemesterEditDTO $dto): ?SemesterListDTO
    {
        $semester = $this->semesterRepository->updateSemester($dto);
        if ($semester) {
            // Reload relationships if needed (for example, holidays)
            $semester->load('holidays');
            return SemesterListDTO::fromModel($semester);
        }
        return null;
    }

    /**
     * Delete a semester by its ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteSemester(int $id): bool
    {
        return $this->semesterRepository->deleteSemester($id);
    }

}
