<?php

namespace App\Services;

use App\Helpers\LetterGradeHelper;

/**
 * The gradebook's calculations for one course section, with no database access,
 * so saved grades, what-if previews and "needed to pass" answers are all worked
 * out the same way.
 *
 * A student's grades are given as [gradeable item id => grade value or null],
 * in grade id order. An item missing from the array has no grade row for the
 * student, so it stays out of its category's totals.
 */
class GradebookCalculator
{
    /**
     * Passing mark for a student whose major has no passing grade set for the
     * course and semester: the lowest D.
     */
    public const DEFAULT_PASSING_GRADE = 60.0;

    /**
     * Enrollment statuses whose final grade is replaced by a letter.
     */
    private const STATUS_LETTERS = [
        'withdrawn' => 'W',
        'unofficial_withdrawn' => 'UW',
        'incomplete' => 'I',
    ];

    /**
     * The categories by id, in table order: weight, algorithm and each item's
     * max points by item id.
     *
     * @var array<int, array{weight: mixed, algorithm: string, maxPoints: array<int, mixed>}>
     */
    protected array $categories = [];

    /**
     * @param iterable $categories The section's GradeableCategory models in table
     *        order, with their gradeableItems loaded.
     * @param string|null $curveAlgorithm The section's curve: AVERAGE_BASED, or null for none.
     */
    public function __construct(iterable $categories, protected ?string $curveAlgorithm)
    {
        foreach ($categories as $category) {
            $this->categories[$category->id] = [
                'weight' => $category->weight_percent,
                'algorithm' => $category->algorithm,
                'maxPoints' => $category->gradeableItems->pluck('max_points', 'id')->all(),
            ];
        }
    }

    /**
     * The letter that replaces the final grade for an enrollment status (W, UW
     * or I), or null when the status keeps its calculated grade.
     */
    public static function statusLetter(string $statusName): ?string
    {
        return self::STATUS_LETTERS[strtolower($statusName)] ?? null;
    }

    /**
     * Whether the item belongs to one of the section's categories.
     */
    public function hasItem(int $itemId): bool
    {
        return $this->maxPoints($itemId) !== null;
    }

    /**
     * The item's max points, or null when the item isn't in the section.
     */
    public function maxPoints(int $itemId): ?float
    {
        foreach ($this->categories as $category) {
            if (array_key_exists($itemId, $category['maxPoints'])) {
                return (float) $category['maxPoints'][$itemId];
            }
        }

        return null;
    }

    /**
     * A category's percentage (0-100, 2 decimals) for one student, or null when
     * the student has no grade row in it.
     *
     * AVERAGE: points earned ÷ points possible over the student's grade rows,
     * where an empty grade counts as 0. PICK_HIGHEST: the best graded item's
     * percentage.
     *
     * @param int $categoryId
     * @param array<int, float|null> $values The student's grades by item id.
     * @return float|null
     */
    public function categoryPercentage(int $categoryId, array $values): ?float
    {
        $category = $this->categories[$categoryId];
        $rows = array_intersect_key($values, $category['maxPoints']);

        if (empty($rows)) {
            return null;
        }

        if ($category['algorithm'] === 'AVERAGE') {
            $totalPoints = 0;
            $maxPoints = 0;

            foreach ($rows as $itemId => $value) {
                $totalPoints += $value ?? 0;
                $maxPoints += $category['maxPoints'][$itemId];
            }

            return $maxPoints > 0 ? round(($totalPoints / $maxPoints) * 100, 2) : null;
        }

        if ($category['algorithm'] === 'PICK_HIGHEST') {
            $percentages = [];

            foreach ($rows as $itemId => $value) {
                if ($value === null) {
                    continue;
                }

                $maxPoints = $category['maxPoints'][$itemId];
                $percentages[] = $maxPoints > 0 ? ($value / $maxPoints) * 100 : 0;
            }

            return empty($percentages) ? null : round(max($percentages), 2);
        }

        return null;
    }

    /**
     * One student's weighted category scores (2 decimals), final grade (a whole
     * number) and letter. A category adds to the final grade once one of its
     * items is graded; until then its score is 0.
     *
     * @param array<int, float|null> $values The student's grades by item id.
     * @return array{categories: array<int, float>, finalGrade: float, letterGrade: string}
     */
    public function studentGrades(array $values): array
    {
        $scores = [];

        foreach ($this->categories as $categoryId => $category) {
            $score = 0.00;
            $graded = array_filter(
                array_intersect_key($values, $category['maxPoints']),
                fn ($value) => $value !== null
            );

            if (!empty($graded)) {
                $percentage = $this->categoryPercentage($categoryId, $values);
                if ($percentage !== null) {
                    $score = round(($percentage * $category['weight'] / 100), 2);
                }
            }

            $scores[$categoryId] = $score;
        }

        $finalGrade = round(array_sum($scores), 0);

        return [
            'categories' => $scores,
            'finalGrade' => $finalGrade,
            'letterGrade' => LetterGradeHelper::calculate($finalGrade),
        ];
    }

    /**
     * The class average (2 decimals) of the final grades above 0. W, UW and I
     * have no final grade, so they're left out too.
     *
     * @param array<float|null> $finalGrades
     * @return float
     */
    public function classAverage(array $finalGrades): float
    {
        $validGrades = array_filter($finalGrades, fn ($grade) => $grade !== null && $grade > 0);

        return count($validGrades) > 0
            ? round(array_sum($validGrades) / count($validGrades), 2)
            : 0.00;
    }

    /**
     * Points the curve adds for a class average. AVERAGE_BASED adds 1 from 80,
     * 2 from 75, 3 from 70 and 4 below that. Without a curve, nothing.
     */
    public function curveAdjustment(float $classAverage): float
    {
        if ($this->curveAlgorithm !== 'AVERAGE_BASED') {
            return 0.0;
        }

        if ($classAverage >= 80.00) {
            return 1.0;
        }
        if ($classAverage >= 75.00) {
            return 2.0;
        }
        if ($classAverage >= 70.00) {
            return 3.0;
        }

        return 4.0;
    }

    /**
     * A final grade after the curve: rounded to a whole number, plus the
     * adjustment, at most 100. Without a curve it stays as it is.
     */
    public function curvedGrade(float $finalGrade, float $adjustment): float
    {
        if ($this->curveAlgorithm !== 'AVERAGE_BASED') {
            return $finalGrade;
        }

        return min(round($finalGrade, 0) + $adjustment, 100.00);
    }

    /**
     * The lowest grade on one item that brings a student to a passing mark, in
     * steps of 0.01 like the grades themselves, with the other students' final
     * grades as they are. The curved grade is the one compared with the mark
     * (without a curve it's the final grade), and the class average behind the
     * curve includes this student's new final grade.
     *
     * @param array<int, float|null> $values The student's grades by item id.
     * @param int $itemId The item to find the grade for.
     * @param float $passingGrade
     * @param array<float|null> $otherFinalGrades The other students' final grades.
     * @return array{status: string, value: float|null, best: float|null}
     *         status "passing": passes even with 0 on the item.
     *         status "needed": value is the lowest grade that passes.
     *         status "cannot": full marks don't pass either; best is the grade they give.
     */
    public function neededGrade(array $values, int $itemId, float $passingGrade, array $otherFinalGrades): array
    {
        $maxCents = (int) round($this->maxPoints($itemId) * 100);

        $finalGradeWith = function (int $cents) use ($values, $itemId): float {
            $values[$itemId] = $cents / 100;
            return $this->studentGrades($values)['finalGrade'];
        };
        $passes = fn (float $finalGrade): bool =>
            $this->comparedGrade($finalGrade, $otherFinalGrades) >= $passingGrade;

        $lowest = $finalGradeWith(0);
        if ($passes($lowest)) {
            return ['status' => 'passing', 'value' => 0.0, 'best' => null];
        }

        // The final grade never drops as the item's grade grows, but passing
        // isn't always one threshold: a higher class average can shrink the
        // curve. So try each final grade that would pass, lowest first, and find
        // the lowest item grade that reaches it.
        $highest = $finalGradeWith($maxCents);
        for ($target = $lowest + 1; $target <= $highest; $target++) {
            if (!$passes($target)) {
                continue;
            }

            $low = 0;
            $high = $maxCents;
            while ($low < $high) {
                $middle = intdiv($low + $high, 2);
                if ($finalGradeWith($middle) >= $target) {
                    $high = $middle;
                } else {
                    $low = $middle + 1;
                }
            }

            // One more cent can skip a final grade, so check the one reached
            $reached = $finalGradeWith($low);
            if ($passes($reached)) {
                return ['status' => 'needed', 'value' => (float) ($low / 100), 'best' => null];
            }
            $target = $reached;
        }

        return [
            'status' => 'cannot',
            'value' => null,
            'best' => $this->comparedGrade($highest, $otherFinalGrades),
        ];
    }

    /**
     * The grade compared with a passing mark: the curved grade, with this final
     * grade counted in the class average.
     *
     * @param float $finalGrade
     * @param array<float|null> $otherFinalGrades
     * @return float
     */
    protected function comparedGrade(float $finalGrade, array $otherFinalGrades): float
    {
        $classAverage = $this->classAverage([...$otherFinalGrades, $finalGrade]);

        return $this->curvedGrade($finalGrade, $this->curveAdjustment($classAverage));
    }
}
