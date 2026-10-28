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

    /**
     * Get every student enrolled in at least one course section, with how many
     * course sections and semesters they had and their latest semester.
     *
     * @return Collection
     */
    public function getStudentsWithCourseCounts(): Collection
    {
        // The student's semester with the latest start date
        $latestSemester = fn (string $column) => DB::table('course_enrollments as ce')
            ->join('course_sections as cs', 'cs.id', '=', 'ce.course_section_id')
            ->join('semesters as sem', 'sem.id', '=', 'cs.semester_id')
            ->whereColumn('ce.student_id', 'students.id')
            ->orderByDesc('sem.start_date')
            ->orderByDesc('sem.id')
            ->limit(1)
            ->select("sem.{$column}");

        return DB::table('students')
            ->join('course_enrollments', 'course_enrollments.student_id', '=', 'students.id')
            ->join('course_sections', 'course_sections.id', '=', 'course_enrollments.course_section_id')
            ->leftJoin('majors', 'majors.id', '=', 'students.major_id')
            ->select(
                'students.id',
                'students.student_id',
                'students.first_name',
                'students.father_name',
                'students.last_name',
                DB::raw('COALESCE(majors.label, majors.system_name, students.major) as major'),
                'students.email',
                'students.campus',
                DB::raw('COUNT(course_enrollments.id) as courses_count'),
                DB::raw('COUNT(DISTINCT course_sections.semester_id) as semesters_count')
            )
            ->selectSub($latestSemester('id'), 'last_semester_id')
            ->selectSub($latestSemester('name'), 'last_semester_name')
            ->selectSub($latestSemester('start_date'), 'last_semester_start_date')
            ->groupBy(
                'students.id',
                'students.student_id',
                'students.first_name',
                'students.father_name',
                'students.last_name',
                'students.major',
                'majors.label',
                'majors.system_name',
                'students.email',
                'students.campus'
            )
            ->orderBy('students.last_name', 'asc')
            ->orderBy('students.first_name', 'asc')
            ->get();
    }

    /**
     * Find a student with their current major's display name.
     *
     * @param int $id The students.id primary key.
     * @return object|null
     */
    public function findWithMajor(int $id): ?object
    {
        return DB::table('students')
            ->leftJoin('majors', 'majors.id', '=', 'students.major_id')
            ->where('students.id', '=', $id)
            ->select(
                'students.id',
                'students.student_id',
                'students.first_name',
                'students.father_name',
                'students.last_name',
                DB::raw('COALESCE(majors.label, majors.system_name, students.major) as major'),
                'students.email',
                'students.campus'
            )
            ->first();
    }

    /**
     * Get a student's course sections, newest semester first, with each one's
     * course, semester, enrollment status and attendance counts.
     *
     * @param int $studentId The students.id primary key.
     * @return Collection
     */
    public function getCourseHistory(int $studentId): Collection
    {
        return DB::table('course_enrollments')
            ->join('course_sections', 'course_sections.id', '=', 'course_enrollments.course_section_id')
            ->join('courses', 'courses.id', '=', 'course_sections.course_id')
            ->join('semesters', 'semesters.id', '=', 'course_sections.semester_id')
            ->leftJoin('student_course_statuses', 'student_course_statuses.id', '=', 'course_enrollments.status_id')
            ->leftJoin('attendances', 'attendances.course_enrollment_id', '=', 'course_enrollments.id')
            ->where('course_enrollments.student_id', '=', $studentId)
            ->select(
                'course_enrollments.id as enrollment_id',
                'course_sections.id as course_section_id',
                'course_sections.section_code',
                'course_sections.time',
                'courses.code as course_code',
                'courses.name as course_name',
                'semesters.id as semester_id',
                'semesters.name as semester_name',
                'semesters.start_date as semester_start_date',
                'semesters.end_date as semester_end_date',
                'student_course_statuses.name as status_name',
                'student_course_statuses.label as status_label',
                DB::raw("SUM(CASE WHEN attendances.value = 'abscent' THEN 1 ELSE 0 END) as absences"),
                DB::raw('SUM(CASE WHEN attendances.value IS NOT NULL THEN 1 ELSE 0 END) as sessions_marked'),
                DB::raw('COUNT(attendances.id) as total_sessions')
            )
            ->groupBy(
                'course_enrollments.id',
                'course_sections.id',
                'course_sections.section_code',
                'course_sections.time',
                'courses.code',
                'courses.name',
                'semesters.id',
                'semesters.name',
                'semesters.start_date',
                'semesters.end_date',
                'student_course_statuses.name',
                'student_course_statuses.label'
            )
            ->orderByDesc('semesters.start_date')
            ->orderBy('courses.code')
            ->orderBy('course_sections.section_code')
            ->get();
    }
}
