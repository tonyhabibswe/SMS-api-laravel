<?php

namespace App\Repositories;

use App\Models\StudentMajorHistory;
use Illuminate\Support\Collection;

class StudentMajorHistoryRepository
{
    /**
     * Create a new student major history record.
     *
     * @param int $studentId
     * @param int $majorId
     * @param int $semesterId
     * @return StudentMajorHistory
     */
    public function createHistory(int $studentId, int $majorId, int $semesterId): StudentMajorHistory
    {
        return StudentMajorHistory::create([
            'student_id' => $studentId,
            'major_id' => $majorId,
            'semester_id' => $semesterId,
        ]);
    }

    /**
     * Get student major history by student ID.
     *
     * @param int $studentId
     * @return Collection
     */
    public function getHistoryByStudentId(int $studentId): Collection
    {
        return StudentMajorHistory::with(['major', 'semester'])
            ->where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get the latest major for a student in a specific semester.
     *
     * @param int $studentId
     * @param int $semesterId
     * @return StudentMajorHistory|null
     */
    public function getLatestMajorForStudentInSemester(int $studentId, int $semesterId): ?StudentMajorHistory
    {
        return StudentMajorHistory::with('major')
            ->where('student_id', $studentId)
            ->where('semester_id', $semesterId)
            ->latest('created_at')
            ->first();
    }

    /**
     * Get the latest major for a student across all semesters.
     *
     * @param int $studentId
     * @return StudentMajorHistory|null
     */
    public function getLatestMajorForStudent(int $studentId): ?StudentMajorHistory
    {
        return StudentMajorHistory::with(['major', 'semester'])
            ->where('student_id', $studentId)
            ->latest('created_at')
            ->first();
    }

    /**
     * Check if a history record already exists.
     *
     * @param int $studentId
     * @param int $majorId
     * @param int $semesterId
     * @return bool
     */
    public function historyExists(int $studentId, int $majorId, int $semesterId): bool
    {
        return StudentMajorHistory::where('student_id', $studentId)
            ->where('major_id', $majorId)
            ->where('semester_id', $semesterId)
            ->exists();
    }

    /**
     * Get all history records for a major.
     *
     * @param int $majorId
     * @return Collection
     */
    public function getHistoryByMajorId(int $majorId): Collection
    {
        return StudentMajorHistory::with(['student', 'semester'])
            ->where('major_id', $majorId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get major history summary for a semester.
     *
     * @param int $semesterId
     * @return Collection
     */
    public function getMajorHistoryBySemester(int $semesterId): Collection
    {
        return StudentMajorHistory::with(['student', 'major'])
            ->where('semester_id', $semesterId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
