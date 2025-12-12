<?php

namespace App\Services;

use App\Repositories\StudentCourseStatusRepository;
use App\Models\StudentCourseStatus;
use Illuminate\Support\Collection;

class StudentCourseStatusService
{
    protected StudentCourseStatusRepository $repository;

    public function __construct(StudentCourseStatusRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all statuses.
     */
    public function getAllStatuses(): Collection
    {
        return $this->repository->getAll();
    }

    /**
     * Get status by ID.
     */
    public function getStatusById(int $id): ?StudentCourseStatus
    {
        return $this->repository->findById($id);
    }

    /**
     * Get status by name.
     */
    public function getStatusByName(string $name): ?StudentCourseStatus
    {
        return $this->repository->findByName($name);
    }

    /**
     * Create a new status.
     */
    public function createStatus(array $data): StudentCourseStatus
    {
        // Validate unique name
        if ($this->repository->findByName($data['name'])) {
            throw new \Exception("Status with this name already exists.");
        }

        return $this->repository->create($data);
    }

    /**
     * Update a status.
     */
    public function updateStatus(int $id, array $data): StudentCourseStatus
    {
        $status = $this->repository->findByIdOrFail($id);

        // Validate unique name if changing
        if (isset($data['name']) && $data['name'] !== $status->name) {
            if ($this->repository->findByName($data['name'])) {
                throw new \Exception("Status with this name already exists.");
            }
        }

        return $this->repository->update($status, $data);
    }

    /**
     * Delete a status.
     */
    public function deleteStatus(int $id): bool
    {
        $status = $this->repository->findByIdOrFail($id);

        return $this->repository->delete($status);
    }
}
