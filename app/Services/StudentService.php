<?php

namespace App\Services;

use App\DTOs\CourseSection\GradesTableRowDTO;
use App\DTOs\Student\StudentCourseDTO;
use App\DTOs\Student\StudentHistoryDTO;
use App\DTOs\Student\StudentListDTO;
use App\Exceptions\UserFacingException;
use App\Repositories\StudentRepository;
use Illuminate\Support\Collection;

class StudentService
{
    protected StudentRepository $studentRepository;
    protected GradeService $gradeService;

    public function __construct(StudentRepository $studentRepository, GradeService $gradeService)
    {
        $this->studentRepository = $studentRepository;
        $this->gradeService = $gradeService;
    }

    /**
     * List the students who took at least one course section.
     *
     * @return Collection|StudentListDTO[]
     */
    public function listStudents(): Collection
    {
        return $this->studentRepository->getStudentsWithCourseCounts()
            ->map(fn ($row) => StudentListDTO::fromRow($row));
    }

    /**
     * Get a student and the course sections they took, newest semester first.
     *
     * @param int $id The students.id primary key.
     * @return StudentHistoryDTO
     * @throws UserFacingException if the student doesn't exist.
     */
    public function getStudentHistory(int $id): StudentHistoryDTO
    {
        $student = $this->studentRepository->findWithMajor($id);
        if (!$student) {
            throw new UserFacingException('Student not found', 404);
        }

        $rows = $this->studentRepository->getCourseHistory($id);

        // Take each grade from its section's gradebook table, which applies the
        // curve (it depends on the class average) and the W/UW/I letters, so the
        // numbers match the gradebook
        $gradebookRows = $rows->pluck('course_section_id')->unique()->mapWithKeys(
            fn ($sectionId) => [
                $sectionId => collect($this->gradeService->getGradesTableForCourseSection($sectionId)->rows)
                    ->keyBy('enrollmentId'),
            ]
        );

        $courses = $rows->map(function ($row) use ($gradebookRows) {
            [$finalGrade, $letterGrade] = $this->shownGrades(
                $gradebookRows[$row->course_section_id]->get($row->enrollment_id)
            );

            return StudentCourseDTO::fromRow($row, $finalGrade, $letterGrade);
        })->values()->all();

        return new StudentHistoryDTO(
            (int) $student->id,
            (string) $student->student_id,
            (string) $student->first_name,
            (string) $student->father_name,
            (string) $student->last_name,
            $student->major,
            (string) $student->email,
            (string) $student->campus,
            count($courses),
            $rows->pluck('semester_id')->unique()->count(),
            $courses
        );
    }

    /**
     * The final grade and letter the gradebook shows for a student: nothing
     * until one of their items is graded, W, UW or I without a grade, otherwise
     * the curved values (the same as the plain ones when there's no curve).
     *
     * @param GradesTableRowDTO|null $row
     * @return array{0: float|null, 1: string|null}
     */
    protected function shownGrades(?GradesTableRowDTO $row): array
    {
        if (!$row) {
            return [null, null];
        }

        if ($row->finalGrade === null) {
            return [null, $row->curvedLetterGrade];
        }

        $graded = collect($row->grades)->contains(
            fn ($value, $key) => str_starts_with($key, 'item_') && $value !== null
        );

        return $graded ? [$row->curvedFinalGrade, $row->curvedLetterGrade] : [null, null];
    }
}
