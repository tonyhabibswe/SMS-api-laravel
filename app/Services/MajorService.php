<?php

namespace App\Services;

use App\DTOs\Major\MajorListDTO;
use App\Models\Major;
use App\Repositories\MajorRepository;
use Illuminate\Support\Collection;

class MajorService
{
    protected MajorRepository $majorRepository;

    public function __construct(MajorRepository $majorRepository)
    {
        $this->majorRepository = $majorRepository;
    }

    /**
     * Find or create a major by system name.
     *
     * @param string $systemName
     * @param string|null $label
     * @return Major
     */
    public function findOrCreateMajor(string $systemName, ?string $label = null): Major
    {
        return $this->majorRepository->findOrCreateMajor($systemName, $label);
    }

    /**
     * Get all majors as DTOs.
     *
     * @return Collection|MajorListDTO[]
     */
    public function listMajors(): Collection
    {
        $majors = $this->majorRepository->getAllMajors();

        return $majors->map(function ($major) {
            return MajorListDTO::fromModel($major);
        });
    }

    /**
     * Get majors with student count.
     *
     * @return Collection|MajorListDTO[]
     */
    public function listMajorsWithStudentCount(): Collection
    {
        $majors = $this->majorRepository->getMajorsWithStudentCount();

        return $majors->map(function ($major) {
            return MajorListDTO::fromModelWithStudentCount($major);
        });
    }

    /**
     * Update major label.
     *
     * @param int $majorId
     * @param string $label
     * @return MajorListDTO|null
     */
    public function updateMajorLabel(int $majorId, string $label): ?MajorListDTO
    {
        $major = $this->majorRepository->updateLabel($majorId, $label);

        return $major ? MajorListDTO::fromModel($major) : null;
    }

    /**
     * Find major by system name.
     *
     * @param string $systemName
     * @return MajorListDTO|null
     */
    public function findMajorBySystemName(string $systemName): ?MajorListDTO
    {
        $major = $this->majorRepository->findBySystemName($systemName);

        return $major ? MajorListDTO::fromModel($major) : null;
    }
}
