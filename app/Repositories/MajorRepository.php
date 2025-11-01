<?php

namespace App\Repositories;

use App\Models\Major;
use Illuminate\Support\Collection;

class MajorRepository
{
    /**
     * Find a major by system_name or create one if it doesn't exist.
     *
     * @param string $systemName
     * @param string|null $label
     * @return Major
     */
    public function findOrCreateMajor(string $systemName, ?string $label = null): Major
    {
        $major = Major::where('system_name', $systemName)->first();

        if (!$major) {
            $major = Major::create([
                'system_name' => $systemName,
                'label' => $label,
            ]);
        }

        return $major;
    }

    /**
     * Get all majors.
     *
     * @return Collection
     */
    public function getAllMajors(): Collection
    {
        return Major::orderBy('label')
            ->orderBy('system_name')
            ->get();
    }

    /**
     * Find a major by system_name.
     *
     * @param string $systemName
     * @return Major|null
     */
    public function findBySystemName(string $systemName): ?Major
    {
        return Major::where('system_name', $systemName)->first();
    }

    /**
     * Update major label.
     *
     * @param int $majorId
     * @param string $label
     * @return Major|null
     */
    public function updateLabel(int $majorId, string $label): ?Major
    {
        $major = Major::find($majorId);

        if ($major) {
            $major->update(['label' => $label]);
        }

        return $major;
    }

    /**
     * Get majors with student count.
     *
     * @return Collection
     */
    public function getMajorsWithStudentCount(): Collection
    {
        return Major::withCount('students')
            ->orderBy('label')
            ->orderBy('system_name')
            ->get();
    }
}
