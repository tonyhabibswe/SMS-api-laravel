<?php

namespace App\Repositories;

use App\Models\StudentCourseStatus;
use Illuminate\Database\Eloquent\Collection;

class StudentCourseStatusRepository
{
    /**
     * Get all statuses.
     */
    public function getAll(): Collection
    {
        return StudentCourseStatus::orderBy('id')->get();
    }

    /**
     * Find status by ID.
     */
    public function findById(int $id): ?StudentCourseStatus
    {
        return StudentCourseStatus::find($id);
    }

    /**
     * Find status by ID or fail.
     */
    public function findByIdOrFail(int $id): StudentCourseStatus
    {
        return StudentCourseStatus::findOrFail($id);
    }

    /**
     * Find status by name.
     */
    public function findByName(string $name): ?StudentCourseStatus
    {
        return StudentCourseStatus::where('name', $name)->first();
    }

    /**
     * Create a new status.
     */
    public function create(array $data): StudentCourseStatus
    {
        return StudentCourseStatus::create($data);
    }

    /**
     * Update a status.
     */
    public function update(StudentCourseStatus $status, array $data): StudentCourseStatus
    {
        $status->update($data);
        return $status->fresh();
    }

    /**
     * Delete a status.
     */
    public function delete(StudentCourseStatus $status): bool
    {
        return $status->delete();
    }
}
