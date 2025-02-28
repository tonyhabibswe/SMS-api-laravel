<?php

namespace App\Repositories;

use App\DTOs\Student\StudentCreateDTO;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudentRepository
{
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
            $student = Student::create([
                'student_id'  => $dto->studentId,
                'first_name'  => $dto->firstName,
                'father_name' => $dto->fatherName,
                'last_name'   => $dto->lastName,
                'major'       => $dto->major,
                'email'       => $dto->email,
                'campus'      => $dto->campus,
            ]);
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
        return Student::whereIn('student_id', $studentIds)->get();
    }

    /**
     * Create a new student from the provided DTO.
     *
     * @param StudentCreateDTO $dto
     * @return Student
     */
    public function createStudentFromDTO(StudentCreateDTO $dto)
    {
        return Student::create([
            'student_id'  => $dto->studentId,
            'first_name'  => $dto->firstName,
            'father_name' => $dto->fatherName,
            'last_name'   => $dto->lastName,
            'major'       => $dto->major,
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
        return DB::table('attendances')
            ->join('course_sessions', 'attendances.course_session_id', '=', 'course_sessions.id')
            ->join('course_sections', 'course_sessions.course_section_id', '=', 'course_sections.id')
            ->join('students', 'attendances.student_id', '=', 'students.id')
            ->where('course_sections.id', '=', $courseSectionId)
            ->select(
                'students.id',
                'students.student_id',
                'students.first_name',
                'students.father_name',
                'students.last_name',
                'students.major',
                'students.email',
                'students.campus',
                DB::raw("SUM(CASE WHEN attendances.value = 'abscent' THEN 1 ELSE 0 END) as abscences"),
                DB::raw('SUM(CASE WHEN attendances.value IS NOT NULL THEN 1 ELSE 0 END) as sessions'),
                DB::raw('COUNT(attendances.id) as total_sessions')
            )
            ->groupBy(
                'students.id',
                'students.student_id',
                'students.first_name',
                'students.father_name',
                'students.last_name',
                'students.major',
                'students.email',
                'students.campus'
            )
            ->get();
    }
}
