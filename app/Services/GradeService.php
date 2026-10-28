<?php

namespace App\Services;

use App\DTOs\Grade\GradeUpdateDTO;
use App\DTOs\Grade\GradeListDTO;
use App\DTOs\Grade\GradeDetailDTO;
use App\DTOs\CourseSection\GradesTableDTO;
use App\DTOs\CourseSection\GradesTableColumnDTO;
use App\DTOs\CourseSection\GradesTableRowDTO;
use App\Exports\GradesTableExport;
use App\Helpers\LetterGradeHelper;
use App\Models\Grade;
use App\Repositories\GradeRepository;
use App\Repositories\GradeableCategoryRepository;
use App\Repositories\CoursePassingGradeRepository;
use App\Repositories\CourseSectionRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;

class GradeService
{
    protected GradeRepository $repository;
    protected GradeableCategoryRepository $categoryRepository;
    protected CourseSectionRepository $courseSectionRepository;
    protected CoursePassingGradeRepository $passingGradeRepository;

    public function __construct(
        GradeRepository $repository,
        GradeableCategoryRepository $categoryRepository,
        CourseSectionRepository $courseSectionRepository,
        CoursePassingGradeRepository $passingGradeRepository
    ) {
        $this->repository = $repository;
        $this->categoryRepository = $categoryRepository;
        $this->courseSectionRepository = $courseSectionRepository;
        $this->passingGradeRepository = $passingGradeRepository;
    }

    /**
     * Get all grades for a specific gradeable item.
     */
    public function getGradesByGradeableItem(int $gradeableItemId): Collection
    {
        $grades = $this->repository->getByGradeableItem($gradeableItemId);
        return $grades->map(fn($grade) => GradeListDTO::fromModel($grade));
    }

    /**
     * Get all grades for a specific enrollment.
     */
    public function getGradesByEnrollment(int $enrollmentId): Collection
    {
        $grades = $this->repository->getByEnrollment($enrollmentId);
        return $grades->map(fn($grade) => GradeListDTO::fromModel($grade));
    }

    /**
     * Update a single grade value with validation. null clears the grade, which
     * then counts as not graded, like a grade that was never entered.
     */
    public function updateGrade(int $id, ?float $gradeValue): GradeDetailDTO
    {
        $grade = $this->repository->findByIdOrFail($id);

        // Validate grade_value is within 0 to max_points
        $maxPoints = $grade->gradeableItem->max_points;

        if ($gradeValue !== null && ($gradeValue < 0 || $gradeValue > $maxPoints)) {
            throw ValidationException::withMessages([
                'gradeValue' => [
                    sprintf('Grade value must be between 0 and %.2f (max points for this item).', $maxPoints)
                ]
            ]);
        }

        $grade = $this->repository->update($id, ['grade_value' => $gradeValue]);

        return GradeDetailDTO::fromModel($grade);
    }

    /**
     * Bulk update multiple grades at once. A grade the student doesn't have yet
     * is created, as long as the item is in the student's course section.
     *
     * @param array $grades Array of ['enrollmentId' => int, 'gradeableItemId' => int, 'gradeValue' => float]
     * @throws ValidationException if an item isn't in the student's course section.
     */
    public function bulkUpdateGrades(array $grades): Collection
    {
        return DB::transaction(function () use ($grades) {
            $updatedGrades = collect();

            foreach ($grades as $index => $gradeData) {
                // Find the grade by enrollmentId and gradeableItemId, or create it
                $grade = $this->repository->findOrCreateByEnrollmentAndItem(
                    $gradeData['enrollmentId'],
                    $gradeData['gradeableItemId']
                );

                if (!$grade) {
                    throw ValidationException::withMessages([
                        "grades.{$index}.gradeableItemId" => ["This item is not in the student's course section."],
                    ]);
                }

                // Update the grade using the existing updateGrade method
                $updatedGrade = $this->updateGrade($grade->id, $gradeData['gradeValue']);
                $updatedGrades->push($updatedGrade);
            }

            return $updatedGrades;
        });
    }

    /**
     * Calculate final grade for an enrollment based on weighted categories.
     */
    public function calculateFinalGrade(int $enrollmentId, int $courseSectionId): ?float
    {
        // Get all categories for this course section
        $categories = $this->categoryRepository->getByCourseSection($courseSectionId);

        if ($categories->isEmpty()) {
            return null;
        }

        // Get all grades for this enrollment
        $allGrades = $this->repository->getByEnrollment($enrollmentId);

        if ($allGrades->isEmpty()) {
            return null;
        }

        $totalWeightedGrade = 0;
        $totalWeight = 0;

        foreach ($categories as $category) {
            // Get grades for this category
            $categoryGrades = $allGrades->filter(function ($grade) use ($category) {
                return $grade->gradeableItem->category_id === $category->id;
            })->filter(fn($grade) => $grade->grade_value !== null);

            if ($categoryGrades->isEmpty()) {
                continue;
            }

            // Calculate category grade based on algorithm
            $categoryGrade = null;

            if ($category->algorithm === 'AVERAGE') {
                $totalPoints = 0;
                $maxPoints = 0;

                foreach ($categoryGrades as $grade) {
                    $totalPoints += $grade->grade_value;
                    $maxPoints += $grade->gradeableItem->max_points;
                }

                $categoryGrade = $maxPoints > 0 ? ($totalPoints / $maxPoints) * 100 : null;
            } elseif ($category->algorithm === 'PICK_HIGHEST') {
                $percentages = $categoryGrades->map(function ($grade) {
                    $maxPoints = $grade->gradeableItem->max_points;
                    return $maxPoints > 0 ? ($grade->grade_value / $maxPoints) * 100 : 0;
                });

                $categoryGrade = $percentages->max();
            }

            if ($categoryGrade !== null) {
                $totalWeightedGrade += ($categoryGrade * $category->weight_percent / 100);
                $totalWeight += $category->weight_percent;
            }
        }

        // Return weighted average
        return $totalWeight > 0 ? round($totalWeightedGrade, 2) : null;
    }

    /**
     * Get grades table for course section with all students and calculations.
     */
    public function getGradesTableForCourseSection(int $courseSectionId): GradesTableDTO
    {
        return $this->buildGradesTable($courseSectionId);
    }

    /**
     * Get the grades table as it would be with some grades changed, saving
     * nothing. With an item, each row also gives the lowest grade the student
     * needs on it to reach their passing mark.
     *
     * @param int $courseSectionId
     * @param array $grades Grades to use instead of the saved ones:
     *        [['enrollmentId' => int, 'gradeableItemId' => int, 'gradeValue' => float|null], ...]
     *        A null gradeValue means not graded.
     * @param int|null $neededItemId The item to give the needed grade on.
     * @return GradesTableDTO
     * @throws ModelNotFoundException if the course section doesn't exist.
     * @throws ValidationException if a grade or the item isn't in the course
     *         section, or a grade is above the item's max points.
     */
    public function previewGradesTable(int $courseSectionId, array $grades, ?int $neededItemId = null): GradesTableDTO
    {
        return $this->buildGradesTable($courseSectionId, $grades, $neededItemId);
    }

    /**
     * Build the grades table from the saved grades, with some replaced for a
     * preview. GradebookCalculator does every calculation.
     */
    protected function buildGradesTable(int $courseSectionId, array $changedGrades = [], ?int $neededItemId = null): GradesTableDTO
    {
        // Retrieve course section with relations
        $courseSection = $this->courseSectionRepository->getCourseSectionWithRelations($courseSectionId);

        if (!$courseSection) {
            throw new ModelNotFoundException("Course section not found");
        }

        // Retrieve categories with items ordered for table
        $categories = $this->categoryRepository->getCategoriesWithItemsByCourseSectionOrderedForTable($courseSectionId);
        $calculator = new GradebookCalculator($categories, $courseSection->curve_algorithm);

        // Each student's grades by item id, in grade id order, with the preview's
        // changes in place of the saved grades
        $gradesGrouped = $this->repository->getAllGradesForCourseSectionGrouped($courseSectionId);
        $valuesByEnrollment = [];
        foreach ($courseSection->courseEnrollments as $enrollment) {
            $valuesByEnrollment[$enrollment->id] = $gradesGrouped->get($enrollment->id, collect())
                ->mapWithKeys(fn ($grade) => [
                    $grade->gradeable_item_id => $grade->grade_value !== null ? (float) $grade->grade_value : null,
                ])
                ->all();
        }
        $this->applyChangedGrades($valuesByEnrollment, $changedGrades, $calculator);

        if ($neededItemId !== null && !$calculator->hasItem($neededItemId)) {
            throw ValidationException::withMessages([
                'neededItemId' => ['This item is not in the course section.'],
            ]);
        }

        // Passing grade by major, for this course and semester
        $passingGrades = $this->passingGradeRepository
            ->getByCourseAndSemester($courseSection->course_id, $courseSection->semester_id)
            ->pluck('grade_value', 'major_id');

        // Build columns array
        $columns = [];

        // Add identifier columns
        $columns[] = new GradesTableColumnDTO('studentId', 'Student ID', 'identifier');
        $columns[] = new GradesTableColumnDTO('studentName', 'Student Name', 'identifier');

        // Add item and category columns
        foreach ($categories as $category) {
            foreach ($category->gradeableItems as $item) {
                $columns[] = new GradesTableColumnDTO(
                    key: "item_{$item->id}",
                    label: $item->title,
                    type: 'gradeableItem',
                    gradeableItemId: $item->id,
                    categoryId: $category->id,
                    categoryName: $category->name,
                    maxPoints: (float) $item->max_points
                );
            }

            // Add category column after all its items
            $weightFormatted = fmod($category->weight_percent, 1) === 0.0
                ? number_format($category->weight_percent, 0)
                : rtrim(rtrim(number_format($category->weight_percent, 2), '0'), '.');

            $columns[] = new GradesTableColumnDTO(
                key: "category_{$category->id}",
                label: "{$category->name} ({$weightFormatted}%)",
                type: 'category',
                categoryId: $category->id,
                weightPercent: (float) $category->weight_percent,
                algorithm: $category->algorithm
            );
        }

        // Add final grade column
        $columns[] = new GradesTableColumnDTO('finalGrade', 'Final Grade', 'calculated');

        // Add letter grade column
        $columns[] = new GradesTableColumnDTO('letterGrade', 'Letter Grade', 'calculated');

        // Add curved grade columns
        $columns[] = new GradesTableColumnDTO('curvedFinalGrade', 'Curved Final Grade', 'calculated');
        $columns[] = new GradesTableColumnDTO('curvedLetterGrade', 'Curved Letter Grade', 'calculated');

        // Always include all enrollments (removed includeInactive filter)
        $enrollments = $courseSection->courseEnrollments;

        // Sort enrollments by student name
        $enrollments = $enrollments->sortBy([
            fn($a, $b) => strcmp($a->student->last_name ?? '', $b->student->last_name ?? ''),
            fn($a, $b) => strcmp($a->student->first_name ?? '', $b->student->first_name ?? ''),
        ]);

        // Build rows array
        $rows = [];
        foreach ($enrollments as $enrollment) {
            $values = $valuesByEnrollment[$enrollment->id];
            $calculated = $calculator->studentGrades($values);

            // Item grades, each category's weighted score after its items
            $grades = [];
            foreach ($categories as $category) {
                foreach ($category->gradeableItems as $item) {
                    $grades["item_{$item->id}"] = $values[$item->id] ?? null;
                }
                $grades["category_{$category->id}"] = $calculated['categories'][$category->id];
            }

            $finalGrade = $calculated['finalGrade'];
            $letterGrade = $calculated['letterGrade'];

            // W, UW and I replace the final grade (the curve is applied once the
            // class average is known)
            $statusLetter = GradebookCalculator::statusLetter($enrollment->status?->name ?? 'enrolled');
            if ($statusLetter !== null) {
                $finalGrade = null;
                $letterGrade = $statusLetter;
            }

            // Create row DTO
            $rows[] = new GradesTableRowDTO(
                enrollmentId: $enrollment->id,
                studentId: $enrollment->student->student_id ?? '',
                studentName: trim(($enrollment->student->first_name ?? '') . ' ' . ($enrollment->student->last_name ?? '')),
                enrollmentStatus: $enrollment->status_id == 1 ? 'active' : 'inactive',
                grades: $grades,
                finalGrade: $finalGrade,
                letterGrade: $letterGrade,
                curvedFinalGrade: $finalGrade,
                curvedLetterGrade: $letterGrade,
                majorName: $enrollment->major?->label ?? $enrollment->major?->system_name,
                passingGrade: (float) ($passingGrades[$enrollment->major_id] ?? GradebookCalculator::DEFAULT_PASSING_GRADE)
            );
        }

        // Class average from the final grades above 0, then the curve it sets
        $finalGrades = array_map(fn ($row) => $row->finalGrade, $rows);
        $classAverage = $calculator->classAverage($finalGrades);
        $curveAdjustment = $calculator->curveAdjustment($classAverage);

        foreach ($rows as $row) {
            // Skip status-based overrides (already have NULL grades)
            if ($row->finalGrade === null) {
                continue;
            }

            $row->curvedFinalGrade = $calculator->curvedGrade($row->finalGrade, $curveAdjustment);
            $row->curvedLetterGrade = LetterGradeHelper::calculate($row->curvedFinalGrade);
        }

        // The lowest grade each student needs on the item, with everyone else's
        // final grade as it is
        if ($neededItemId !== null) {
            foreach ($rows as $index => $row) {
                if ($row->finalGrade === null) {
                    continue;
                }

                $otherFinalGrades = $finalGrades;
                unset($otherFinalGrades[$index]);
                $row->needed = $calculator->neededGrade(
                    $valuesByEnrollment[$row->enrollmentId],
                    $neededItemId,
                    $row->passingGrade,
                    $otherFinalGrades
                );
            }
        }

        // Create and return table DTO
        return new GradesTableDTO(
            courseSectionId: $courseSection->id,
            courseName: ($courseSection->course->code ?? '') . ' - ' . ($courseSection->course->name ?? ''),
            semesterName: $courseSection->semester->name ?? '',
            totalStudents: count($rows),
            classAverage: $classAverage,
            curveAlgorithm: $courseSection->curve_algorithm,
            curveAdjustment: $curveAdjustment,
            columns: $columns,
            rows: $rows
        );
    }

    /**
     * Put a preview's grades in place of the saved ones, after checking that
     * each is for a student and an item of the course section and fits the
     * item's max points.
     *
     * @param array $valuesByEnrollment Grades by item id, by enrollment id.
     * @param array $changedGrades [['enrollmentId' => int, 'gradeableItemId' => int, 'gradeValue' => float|null], ...]
     * @param GradebookCalculator $calculator
     * @throws ValidationException
     */
    protected function applyChangedGrades(array &$valuesByEnrollment, array $changedGrades, GradebookCalculator $calculator): void
    {
        $errors = [];

        foreach (array_values($changedGrades) as $index => $change) {
            $enrollmentId = (int) $change['enrollmentId'];
            $itemId = (int) $change['gradeableItemId'];
            $gradeValue = $change['gradeValue'] !== null ? (float) $change['gradeValue'] : null;
            $maxPoints = $calculator->maxPoints($itemId);

            if (!array_key_exists($enrollmentId, $valuesByEnrollment)) {
                $errors["grades.{$index}.enrollmentId"] = ['This student is not enrolled in the course section.'];
            } elseif ($maxPoints === null) {
                $errors["grades.{$index}.gradeableItemId"] = ['This item is not in the course section.'];
            } elseif ($gradeValue !== null && ($gradeValue < 0 || $gradeValue > $maxPoints)) {
                $errors["grades.{$index}.gradeValue"] = [
                    sprintf('Grade value must be between 0 and %.2f (max points for this item).', $maxPoints)
                ];
            } else {
                $valuesByEnrollment[$enrollmentId][$itemId] = $gradeValue;
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Export grades table and attendance to Excel file.
     *
     * @param int $courseSectionId
     * @return array ['fileName' => string, 'file' => base64_encoded_string]
     * @throws ModelNotFoundException
     */
    public function exportGradesTable(int $courseSectionId): array
    {
        // Get the grades table data
        $gradesTableDTO = $this->getGradesTableForCourseSection($courseSectionId);

        // Get course section for filename
        $courseSection = $this->courseSectionRepository->getCourseSectionWithRelations($courseSectionId);

        if (!$courseSection) {
            throw new ModelNotFoundException("Course section not found");
        }

        // Generate filename: "{Course Code} - {Section Code} - Grades - Tony Habib - {Semester}.xlsx"
        $courseCode = $courseSection->course->code ?? 'course';
        $sectionCode = $courseSection->section_code ?? 'section';
        $semesterName = $courseSection->semester->name ?? 'semester';
        $fileName = "{$courseCode} - {$sectionCode} - Grades - Tony Habib - {$semesterName}.xlsx";

        // Create Excel export with both grades and attendance sheets
        $fileContent = ExcelFacade::raw(
            new GradesTableExport($gradesTableDTO, $courseSectionId, $courseSection),
            Excel::XLSX
        );

        $base64File = base64_encode($fileContent);

        return [
            'fileName' => $fileName,
            'file' => $base64File
        ];
    }
}
