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
    protected GradeableCategoryService $gradeableCategoryService;

    public function __construct(
        GradeRepository $repository,
        GradeableCategoryRepository $categoryRepository,
        CourseSectionRepository $courseSectionRepository,
        GradeableCategoryService $gradeableCategoryService
    ) {
        $this->repository = $repository;
        $this->categoryRepository = $categoryRepository;
        $this->courseSectionRepository = $courseSectionRepository;
        $this->gradeableCategoryService = $gradeableCategoryService;
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
     * Update a single grade value with validation.
     */
    public function updateGrade(int $id, float $gradeValue): GradeDetailDTO
    {
        $grade = $this->repository->findByIdOrFail($id);

        // Validate grade_value is within 0 to max_points
        $maxPoints = $grade->gradeableItem->max_points;

        if ($gradeValue < 0 || $gradeValue > $maxPoints) {
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
     * Bulk update multiple grades at once.
     *
     * @param array $grades Array of ['enrollmentId' => int, 'gradeableItemId' => int, 'gradeValue' => float]
     */
    public function bulkUpdateGrades(array $grades): Collection
    {
        return DB::transaction(function () use ($grades) {
            $updatedGrades = collect();

            foreach ($grades as $gradeData) {
                // Find the grade by enrollmentId and gradeableItemId
                $grade = $this->repository->findByEnrollmentAndItemOrFail(
                    $gradeData['enrollmentId'],
                    $gradeData['gradeableItemId']
                );

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
     * Check if enrollment status requires grade override.
     */
    protected function requiresGradeOverride(string $statusName): bool
    {
        return in_array(strtolower($statusName), ['withdrawn', 'unofficial_withdrawn', 'incomplete']);
    }

    /**
     * Get letter grade based on status name.
     */
    protected function getStatusLetterGrade(string $statusName): string
    {
        return match (strtolower($statusName)) {
            'withdrawn' => 'W',
            'unofficial_withdrawn' => 'UW',
            'incomplete' => 'I',
            default => '',
        };
    }

    /**
     * Get grades table for course section with all students and calculations.
     */
    public function getGradesTableForCourseSection(int $courseSectionId): GradesTableDTO
    {
        // Retrieve course section with relations
        $courseSection = $this->courseSectionRepository->getCourseSectionWithRelations($courseSectionId);

        if (!$courseSection) {
            throw new ModelNotFoundException("Course section not found");
        }

        // Retrieve categories with items ordered for table
        $categories = $this->categoryRepository->getCategoriesWithItemsByCourseSectionOrderedForTable($courseSectionId);

        // Retrieve all grades grouped by enrollment
        $gradesGrouped = $this->repository->getAllGradesForCourseSectionGrouped($courseSectionId);

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
            $grades = [];
            $categoryWeightedScores = [];

            // Get status name for this enrollment
            $statusName = $enrollment->status?->name ?? 'enrolled';
            $requiresOverride = $this->requiresGradeOverride($statusName);

            // Get grades for this enrollment
            $enrollmentGrades = $gradesGrouped->get($enrollment->id, collect());

            // Populate item grades
            foreach ($categories as $category) {
                $itemGradesForCategory = [];

                foreach ($category->gradeableItems as $item) {
                    $grade = $enrollmentGrades->firstWhere('gradeable_item_id', $item->id);
                    $gradeValue = $grade && $grade->grade_value !== null ? (float) $grade->grade_value : null;
                    $grades["item_{$item->id}"] = $gradeValue;

                    // Store for category calculation
                    if ($gradeValue !== null) {
                        $percentage = ($gradeValue / $item->max_points) * 100;
                        $itemGradesForCategory[] = $percentage;
                    }
                }

                // Calculate category weighted score
                $categoryWeightedScore = 0.00;
                if (!empty($itemGradesForCategory)) {
                    // Use existing calculation logic
                    $categoryPercentage = $this->gradeableCategoryService->calculateCategoryGrade($category->id, $enrollment->id);
                    if ($categoryPercentage !== null) {
                        $categoryWeightedScore = round(($categoryPercentage * $category->weight_percent / 100), 2);
                    }
                }

                $grades["category_{$category->id}"] = $categoryWeightedScore;
                $categoryWeightedScores[] = $categoryWeightedScore;
            }

            // Calculate final grade and letter grade
            $finalGrade = round(array_sum($categoryWeightedScores), 0);
            $letterGrade = LetterGradeHelper::calculate($finalGrade);

            // Apply status-based overrides
            if ($requiresOverride) {
                $statusLetterGrade = $this->getStatusLetterGrade($statusName);
                $finalGrade = null;
                $letterGrade = $statusLetterGrade;
                $curvedFinalGrade = null;
                $curvedLetterGrade = $statusLetterGrade;
            } else {
                // Initialize curved grades (will be recalculated after class average)
                $curvedFinalGrade = $finalGrade;
                $curvedLetterGrade = $letterGrade;
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
                curvedFinalGrade: $curvedFinalGrade,
                curvedLetterGrade: $curvedLetterGrade
            );
        }

        // Calculate class average from valid final grades (excluding NULL and 0.00)
        // Uses arithmetic mean rounded to 2 decimal places
        $validGrades = array_filter(
            array_map(fn($row) => $row->finalGrade, $rows),
            fn($grade) => $grade !== null && $grade > 0
        );

        $classAverage = count($validGrades) > 0
            ? round(array_sum($validGrades) / count($validGrades), 2)
            : 0.00;

        // Retrieve curve algorithm and calculate curve adjustment
        $curveAlgorithm = $courseSection->curve_algorithm;
        $curveAdjustment = 0.0;

        if ($curveAlgorithm === 'AVERAGE_BASED') {
            // Determine curve adjustment based on class average thresholds
            if ($classAverage >= 80.00) {
                $curveAdjustment = 1.0;
            } elseif ($classAverage >= 75.00) {
                $curveAdjustment = 2.0;
            } elseif ($classAverage >= 70.00) {
                $curveAdjustment = 3.0;
            } else {
                $curveAdjustment = 4.0;
            }

            // Apply curve to each row: round final grade to integer, then add adjustment
            foreach ($rows as $row) {
                // Skip status-based overrides (already have NULL grades)
                if ($row->finalGrade === null) {
                    continue;
                }

                $roundedFinalGrade = round($row->finalGrade, 0); // Round to nearest integer
                $row->curvedFinalGrade = min($roundedFinalGrade + $curveAdjustment, 100.00); // Cap at 100.00
                $row->curvedLetterGrade = LetterGradeHelper::calculate($row->curvedFinalGrade);
            }
        } else {
            // No curve applied: curvedFinalGrade equals finalGrade
            foreach ($rows as $row) {
                // Skip status-based overrides (already set)
                if ($row->finalGrade === null) {
                    continue;
                }

                $row->curvedFinalGrade = $row->finalGrade;
                $row->curvedLetterGrade = $row->letterGrade;
            }
        }

        // Create and return table DTO
        return new GradesTableDTO(
            courseSectionId: $courseSection->id,
            courseName: ($courseSection->course->code ?? '') . ' - ' . ($courseSection->course->name ?? ''),
            semesterName: $courseSection->semester->name ?? '',
            totalStudents: count($rows),
            classAverage: $classAverage,
            curveAlgorithm: $curveAlgorithm,
            curveAdjustment: $curveAdjustment,
            columns: $columns,
            rows: $rows
        );
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
