<?php

namespace App\Repositories;

use App\DTOs\Student\StudentCreateDTO;
use App\Models\Student;
use App\Services\MajorService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudentRepository
{
    protected MajorService $majorService;

    public function __construct(MajorService $majorService)
    {
        $this->majorService = $majorService;
    }

    /**
     * Find a student by student_id or create one using the DTO data.
     *
     * @param StudentCreateDTO $dto
     * @return Student
     */
    public function findOrCreateStudent(StudentCreateDTO $dto): Student
    {
        $student = Student::where('student_id', $dto->studentId)->first();
        if (!$student) {
            // Find or create the major
            $major = $this->majorService->findOrCreateMajor($dto->major);

            $student = Student::create([
                'student_id'  => $dto->studentId,
                'first_name'  => $dto->firstName,
                'father_name' => $dto->fatherName,
                'last_name'   => $dto->lastName,
                'major'       => $dto->major, // Keep legacy field
                'major_id'    => $major->id,  // New normalized field
                'email'       => $dto->email,
                'campus'      => $dto->campus,
            ]);
        } else {
            // Update existing student if major changed
            $major = $this->majorService->findOrCreateMajor($dto->major);
            if ($student->major_id !== $major->id) {
                $student->update([
                    'major' => $dto->major,
                    'major_id' => $major->id,
                ]);
            }
        }
        return $student;
    }

    /**
     * Retrieve students with student_id in the given array.
     *
     * @param array $studentIds
     * @return Collection
     */
    public function getStudentsByStudentIds(array $studentIds): Collection
    {
        return Student::with('major')
            ->whereIn('student_id', $studentIds)
            ->get();
    }

    /**
     * Create a new student from the provided DTO.
     *
     * @param StudentCreateDTO $dto
     * @return Student
     */
    public function createStudentFromDTO(StudentCreateDTO $dto): Student
    {
        // Find or create the major
        $major = $this->majorService->findOrCreateMajor($dto->major);

        return Student::create([
            'student_id'  => $dto->studentId,
            'first_name'  => $dto->firstName,
            'father_name' => $dto->fatherName,
            'last_name'   => $dto->lastName,
            'major'       => $dto->major, // Keep legacy field
            'major_id'    => $major->id,  // New normalized field
            'email'       => $dto->email,
            'campus'      => $dto->campus,
        ]);
    }

    /**
     * Get the student attendance summary for a given course section.
     *
     * @param int $courseSectionId
     * @return Collection
     */
    public function getStudentAttendanceSummaryByCourseSectionId(int $courseSectionId): Collection
    {
        return DB::table('course_enrollments')
            ->join('students', 'course_enrollments.student_id', '=', 'students.id')
            ->leftJoin('majors', 'course_enrollments.major_id', '=', 'majors.id')
            ->leftJoin('attendances', function ($join) {
                $join->on('attendances.course_enrollment_id', '=', 'course_enrollments.id');
            })
            ->leftJoin('course_sessions', 'attendances.course_session_id', '=', 'course_sessions.id')
            ->where('course_enrollments.course_section_id', '=', $courseSectionId)
            ->select(
                'course_enrollments.id',
                'students.student_id',
                'students.first_name',
                'students.father_name',
                'students.last_name',
                'students.major as legacy_major',
                DB::raw('COALESCE(majors.label, majors.system_name, students.major) as major'),
                'students.email',
                'students.campus',
                'course_enrollments.status_id as statusId',
                DB::raw("SUM(CASE WHEN attendances.value = 'abscent' THEN 1 ELSE 0 END) as abscences"),
                DB::raw('SUM(CASE WHEN attendances.value IS NOT NULL THEN 1 ELSE 0 END) as sessions'),
                DB::raw('COUNT(attendances.id) as total_sessions')
            )
            ->groupBy(
                'course_enrollments.id',
                'students.student_id',
                'students.first_name',
                'students.father_name',
                'students.last_name',
                'students.major',
                'majors.label',
                'majors.system_name',
                'students.email',
                'students.campus',
                'course_enrollments.status_id'
            )
            ->orderBy('students.last_name', 'asc')
            ->orderBy('students.first_name', 'asc')
            ->get();
    }
}
