<?php

namespace App\Repositories;

use App\Models\GradeableItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GradeableItemRepository
{
    /**
     * Get all gradeable items with relationships.
     */
    public function getAll(): Collection
    {
        return GradeableItem::with(['category', 'grades'])
            ->orderBy('id')
            ->get();
    }

    /**
     * Find a gradeable item by ID with relationships.
     */
    public function findById(int $id): ?GradeableItem
    {
        return GradeableItem::with(['category', 'grades'])->find($id);
    }

    /**
     * Find a gradeable item by ID or throw exception.
     */
    public function findByIdOrFail(int $id): GradeableItem
    {
        return GradeableItem::with(['category', 'grades'])->findOrFail($id);
    }

    /**
     * Get all items for a specific category.
     */
    public function getByCategory(int $categoryId): Collection
    {
        return GradeableItem::with(['category', 'grades'])
            ->where('category_id', $categoryId)
            ->orderBy('title')
            ->get();
    }

    /**
     * Get all items for a specific course section (through category).
     */
    public function getByCourseSection(int $courseSectionId): Collection
    {
        return GradeableItem::with(['category', 'grades'])
            ->whereHas('category', function ($query) use ($courseSectionId) {
                $query->where('course_section_id', $courseSectionId);
            })
            ->orderBy('title')
            ->get();
    }

    /**
     * Create a new gradeable item.
     */
    public function create(array $data): GradeableItem
    {
        return GradeableItem::create($data);
    }

    /**
     * Update a gradeable item.
     */
    public function update(int $id, array $data): GradeableItem
    {
        $item = $this->findByIdOrFail($id);
        $item->update($data);
        return $item->fresh(['category', 'grades']);
    }

    /**
     * Delete a gradeable item.
     */
    public function delete(int $id): bool
    {
        $item = $this->findByIdOrFail($id);
        return $item->delete();
    }
}
