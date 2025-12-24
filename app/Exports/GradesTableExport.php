<?php

namespace App\Exports;

use App\DTOs\CourseSection\GradesTableDTO;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class GradesTableExport implements WithMultipleSheets
{
    protected GradesTableDTO $gradesTableDTO;
    protected int $courseSectionId;

    public function __construct(GradesTableDTO $gradesTableDTO, int $courseSectionId)
    {
        $this->gradesTableDTO = $gradesTableDTO;
        $this->courseSectionId = $courseSectionId;
    }

    public function sheets(): array
    {
        return [
            new GradesSheet($this->gradesTableDTO),
            new AttendanceSheetForGrades($this->courseSectionId),
        ];
    }
}

class GradesSheet implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    protected GradesTableDTO $gradesTableDTO;

    public function __construct(GradesTableDTO $gradesTableDTO)
    {
        $this->gradesTableDTO = $gradesTableDTO;
    }

    public function title(): string
    {
        return 'Grades';
    }

    public function array(): array
    {
        $data = [];

        // Build header row
        $headerRow = [];
        foreach ($this->gradesTableDTO->columns as $column) {
            $headerRow[] = $column->label;
        }
        $data[] = $headerRow;

        // Build data rows
        foreach ($this->gradesTableDTO->rows as $rowIndex => $row) {
            $dataRow = [];
            $excelRowNumber = $rowIndex + 2; // +2 because Excel is 1-indexed and we have a header row

            foreach ($this->gradesTableDTO->columns as $columnIndex => $column) {
                $excelColumnLetter = Coordinate::stringFromColumnIndex($columnIndex + 1);

                if ($column->type === 'identifier') {
                    // Student ID or Student Name
                    if ($column->key === 'studentId') {
                        $dataRow[] = $row->studentId;
                    } elseif ($column->key === 'studentName') {
                        $dataRow[] = $row->studentName;
                    } else {
                        $dataRow[] = '';
                    }
                } elseif ($column->type === 'gradeableItem') {
                    // Gradeable item - raw score
                    $itemKey = $column->key;
                    $gradeValue = $row->grades[$itemKey] ?? null;
                    // Handle null as empty string
                    $dataRow[] = $gradeValue !== null ? $gradeValue : '';
                } elseif ($column->type === 'category') {
                    // Category - weighted score with formula
                    $categoryItemColumns = [];
                    foreach ($this->gradesTableDTO->columns as $idx => $col) {
                        if ($col->type === 'gradeableItem' && $col->categoryId === $column->categoryId) {
                            $categoryItemColumns[] = [
                                'columnIndex' => $idx + 1,
                                'columnLetter' => Coordinate::stringFromColumnIndex($idx + 1),
                                'maxPoints' => $col->maxPoints
                            ];
                        }
                    }

                    if (!empty($categoryItemColumns)) {
                        $algorithm = $column->algorithm ?? 'AVERAGE';
                        $weight = $column->weightPercent;

                        if ($algorithm === 'AVERAGE' || $algorithm === 'PERCENTAGE') {
                            // Build percentage formulas for each item and average them
                            $percentageParts = [];
                            foreach ($categoryItemColumns as $itemCol) {
                                $itemRef = $itemCol['columnLetter'] . $excelRowNumber;
                                $maxPoints = $itemCol['maxPoints'];
                                $percentageParts[] = "({$itemRef}/{$maxPoints}*100)";
                            }
                            $avgFormula = implode('+', $percentageParts);
                            $count = count($categoryItemColumns);

                            $formula = "=ROUND(({$avgFormula})/{$count}*{$weight}/100,2)";

                            $dataRow[] = $formula;
                        } elseif ($algorithm === 'DROP_LOWEST') {
                            // Build percentages, drop lowest, then average
                            $percentageParts = [];
                            foreach ($categoryItemColumns as $itemCol) {
                                $itemRef = $itemCol['columnLetter'] . $excelRowNumber;
                                $maxPoints = $itemCol['maxPoints'];
                                $percentageParts[] = "({$itemRef}/{$maxPoints}*100)";
                            }

                            $sumFormula = implode('+', $percentageParts);
                            $minFormula = 'MIN(' . implode(',', $percentageParts) . ')';
                            $count = count($percentageParts);

                            if ($count > 1) {
                                $formula = "=ROUND(({$sumFormula}-{$minFormula})/(" . ($count - 1) . ")*{$weight}/100,2)";
                            } else {
                                // Single item - cannot drop
                                $formula = "=ROUND(({$sumFormula})/{$count}*{$weight}/100,2)";
                            }

                            $dataRow[] = $formula;
                        } else {
                            // Fallback to precomputed value
                            $dataRow[] = $row->grades[$column->key] ?? '';
                        }
                    } else {
                        $dataRow[] = $row->grades[$column->key] ?? '';
                    }
                } elseif ($column->type === 'calculated') {
                    if ($column->key === 'finalGrade') {
                        // If letter grade is W, UW, or I, show blank
                        $letterGrade = $row->letterGrade ?? '';
                        if (in_array($letterGrade, ['W', 'UW', 'I'])) {
                            $dataRow[] = '';
                        } else {
                            // Sum all category weighted scores
                            $categoryColumns = [];
                            foreach ($this->gradesTableDTO->columns as $idx => $col) {
                                if ($col->type === 'category') {
                                    $categoryColumns[] = Coordinate::stringFromColumnIndex($idx + 1) . $excelRowNumber;
                                }
                            }

                            if (!empty($categoryColumns)) {
                                $formula = "=ROUND(SUM(" . implode(',', $categoryColumns) . "),0)";
                                $dataRow[] = $formula;
                            } else {
                                $dataRow[] = $row->finalGrade ?? '';
                            }
                        }
                    } elseif ($column->key === 'letterGrade') {
                        // Use static letter grade value (includes W, UW, I)
                        $dataRow[] = $row->letterGrade ?? '';
                    } elseif ($column->key === 'curvedFinalGrade') {
                        // If curved letter grade is W, UW, or I, show blank
                        $curvedLetterGrade = $row->curvedLetterGrade ?? '';
                        if (in_array($curvedLetterGrade, ['W', 'UW', 'I'])) {
                            $dataRow[] = '';
                        } else {
                            // Calculate curved final grade with formula
                            $finalGradeColumnIndex = null;
                            foreach ($this->gradesTableDTO->columns as $idx => $col) {
                                if ($col->key === 'finalGrade') {
                                    $finalGradeColumnIndex = $idx + 1;
                                    break;
                                }
                            }

                            if ($finalGradeColumnIndex && $this->gradesTableDTO->curveAlgorithm === 'AVERAGE_BASED') {
                                $finalGradeRef = Coordinate::stringFromColumnIndex($finalGradeColumnIndex) . $excelRowNumber;
                                $curveAdjustment = $this->gradesTableDTO->curveAdjustment ?? 0;
                                $formula = "=MIN({$finalGradeRef}+{$curveAdjustment},100)";
                                $dataRow[] = $formula;
                            } else {
                                $dataRow[] = $row->curvedFinalGrade ?? '';
                            }
                        }
                    } elseif ($column->key === 'curvedLetterGrade') {
                        // Use static curved letter grade value (includes W, UW, I)
                        $dataRow[] = $row->curvedLetterGrade ?? '';
                    } else {
                        $dataRow[] = '';
                    }
                }
            }

            $data[] = $dataRow;
        }

        return $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Get highest column and row
                $highestColumn = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestRow();

                // Find column indices for finalGrade and curvedFinalGrade
                $finalGradeColumnIndex = null;
                $curvedFinalGradeColumnIndex = null;
                $letterGradeColumnIndex = null;
                $curvedLetterGradeColumnIndex = null;
                $categoryColumnIndices = [];

                // Find identifier columns (studentId, studentName)
                $identifierColumnIndices = [];
                foreach ($this->gradesTableDTO->columns as $idx => $col) {
                    if ($col->type === 'identifier') {
                        $identifierColumnIndices[] = $idx + 1;
                    }
                    if ($col->key === 'finalGrade') {
                        $finalGradeColumnIndex = $idx + 1;
                    } elseif ($col->key === 'curvedFinalGrade') {
                        $curvedFinalGradeColumnIndex = $idx + 1;
                    } elseif ($col->key === 'letterGrade') {
                        $letterGradeColumnIndex = $idx + 1;
                    } elseif ($col->key === 'curvedLetterGrade') {
                        $curvedLetterGradeColumnIndex = $idx + 1;
                    } elseif ($col->type === 'category') {
                        $categoryColumnIndices[] = $idx + 1;
                    }
                }

                // Calculate dynamic column positions for averages
                // Skip one column after the table, then place labels and values
                $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
                $labelColumnIndex = $highestColumnIndex + 2; // Skip one column
                $valueColumnIndex = $labelColumnIndex + 1;
                $labelColumn = Coordinate::stringFromColumnIndex($labelColumnIndex);
                $valueColumn = Coordinate::stringFromColumnIndex($valueColumnIndex);

                // Add average labels and formulas to the right of the table
                // Label column, row 1: "Average"
                $sheet->setCellValue("{$labelColumn}1", 'Average');
                $sheet->getStyle("{$labelColumn}1")->getFont()->setBold(true);

                // Label column, row 2: "Final Grade"
                $sheet->setCellValue("{$labelColumn}2", 'Final Grade');
                $sheet->getStyle("{$labelColumn}2")->getFont()->setBold(true);

                // Value column, row 2: Average of Final Grade column with 2 decimal points
                if ($finalGradeColumnIndex) {
                    $finalGradeColLetter = Coordinate::stringFromColumnIndex($finalGradeColumnIndex);
                    $sheet->setCellValue("{$valueColumn}2", "=ROUND(AVERAGE({$finalGradeColLetter}2:{$finalGradeColLetter}{$highestRow}),2)");
                }

                // Label column, row 3: "Curved Final Grade"
                $sheet->setCellValue("{$labelColumn}3", 'Curved Final Grade');
                $sheet->getStyle("{$labelColumn}3")->getFont()->setBold(true);

                // Value column, row 3: Average of Curved Final Grade column with 2 decimal points
                if ($curvedFinalGradeColumnIndex) {
                    $curvedFinalGradeColLetter = Coordinate::stringFromColumnIndex($curvedFinalGradeColumnIndex);
                    $sheet->setCellValue("{$valueColumn}3", "=ROUND(AVERAGE({$curvedFinalGradeColLetter}2:{$curvedFinalGradeColLetter}{$highestRow}),2)");
                }

                // Auto-fit all columns including the new average columns
                for ($col = 1; $col <= $valueColumnIndex; $col++) {
                    $colLetter = Coordinate::stringFromColumnIndex($col);
                    $sheet->getColumnDimension($colLetter)->setAutoSize(true);
                }

                // Apply number format with 2 decimal points to all data cells
                $dataRange = 'A2:' . $highestColumn . $highestRow;
                $sheet->getStyle($dataRange)->getNumberFormat()->setFormatCode('0.00');

                // Remove decimal format from identifier columns (studentId should not have decimals)
                foreach ($identifierColumnIndices as $colIndex) {
                    $colLetter = Coordinate::stringFromColumnIndex($colIndex);
                    $sheet->getStyle("{$colLetter}2:{$colLetter}{$highestRow}")->getNumberFormat()->setFormatCode('@'); // Text format
                }

                // Make category columns bold
                foreach ($categoryColumnIndices as $colIndex) {
                    $colLetter = Coordinate::stringFromColumnIndex($colIndex);
                    $sheet->getStyle("{$colLetter}2:{$colLetter}{$highestRow}")->getFont()->setBold(true);
                }

                // Make finalGrade column bold
                if ($finalGradeColumnIndex) {
                    $colLetter = Coordinate::stringFromColumnIndex($finalGradeColumnIndex);
                    $sheet->getStyle("{$colLetter}2:{$colLetter}{$highestRow}")->getFont()->setBold(true);
                }

                // Make letterGrade column bold
                if ($letterGradeColumnIndex) {
                    $colLetter = Coordinate::stringFromColumnIndex($letterGradeColumnIndex);
                    $sheet->getStyle("{$colLetter}2:{$colLetter}{$highestRow}")->getFont()->setBold(true);
                }

                // Make curvedFinalGrade column bold
                if ($curvedFinalGradeColumnIndex) {
                    $colLetter = Coordinate::stringFromColumnIndex($curvedFinalGradeColumnIndex);
                    $sheet->getStyle("{$colLetter}2:{$colLetter}{$highestRow}")->getFont()->setBold(true);
                }

                // Make curvedLetterGrade column bold
                if ($curvedLetterGradeColumnIndex) {
                    $colLetter = Coordinate::stringFromColumnIndex($curvedLetterGradeColumnIndex);
                    $sheet->getStyle("{$colLetter}2:{$colLetter}{$highestRow}")->getFont()->setBold(true);
                }

                // Apply red color to specific letter grades (W, UW, I, F)
                $redGrades = ['W', 'UW', 'I', 'F'];

                // Color letterGrade column
                if ($letterGradeColumnIndex) {
                    $colLetter = Coordinate::stringFromColumnIndex($letterGradeColumnIndex);
                    for ($row = 2; $row <= $highestRow; $row++) {
                        $cellValue = $sheet->getCell("{$colLetter}{$row}")->getValue();
                        if (in_array($cellValue, $redGrades)) {
                            $sheet->getStyle("{$colLetter}{$row}")->getFont()->getColor()->setRGB('FF0000');
                        }
                    }
                }

                // Color curvedLetterGrade column
                if ($curvedLetterGradeColumnIndex) {
                    $colLetter = Coordinate::stringFromColumnIndex($curvedLetterGradeColumnIndex);
                    for ($row = 2; $row <= $highestRow; $row++) {
                        $cellValue = $sheet->getCell("{$colLetter}{$row}")->getValue();
                        if (in_array($cellValue, $redGrades)) {
                            $sheet->getStyle("{$colLetter}{$row}")->getFont()->getColor()->setRGB('FF0000');
                        }
                    }
                }

                // Style header row
                $headerRange = 'A1:' . $highestColumn . '1';
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Apply borders to entire table
                $tableRange = 'A1:' . $highestColumn . $highestRow;
                $sheet->getStyle($tableRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Center align all cells
                $sheet->getStyle($tableRange)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
            },
        ];
    }
}

class AttendanceSheetForGrades implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    protected int $courseSectionId;
    protected array $monthColumns = [];

    public function __construct(int $courseSectionId)
    {
        $this->courseSectionId = $courseSectionId;
    }

    public function title(): string
    {
        return 'Attendance';
    }

    public function array(): array
    {
        // Reuse the existing AttendanceExport logic
        $attendanceExport = new AttendanceExport($this->courseSectionId);
        $attendanceData = $attendanceExport->array();

        // Store month columns from the attendance export
        $this->monthColumns = $attendanceExport->getMonthColumns();

        return $attendanceData;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Get highest column and row
                $highestColumn = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestRow();

                // Define header range for rows 1 and 2
                $headerRange = 'A1:' . $highestColumn . '2';
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'BFBFBF'],
                    ],
                ]);

                // Merge cells for each month header in row 1
                foreach ($this->monthColumns as $month => $columns) {
                    if (count($columns) > 0) {
                        $startIndex = min($columns);
                        $endIndex = max($columns);
                        $startColumn = Coordinate::stringFromColumnIndex($startIndex);
                        $endColumn = Coordinate::stringFromColumnIndex($endIndex);
                        $range = $startColumn . '1:' . $endColumn . '1';
                        $sheet->mergeCells($range);
                        $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                }

                // Apply alignment for day header row (row 2)
                $dayHeaderRange = 'A2:' . $highestColumn . '2';
                $sheet->getStyle($dayHeaderRange)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setShrinkToFit(true);

                // Apply border styling to entire table
                $tableRange = 'A1:' . $highestColumn . $highestRow;
                $sheet->getStyle($tableRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
