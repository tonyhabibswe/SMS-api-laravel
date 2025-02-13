<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\CourseSession;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AttendanceExport implements FromArray, ShouldAutoSize, WithEvents
{
    protected int $courseSectionId;
    
    // This property stores the mapping of month names to the column indices in the header row.
    protected array $monthColumns = [];

    public function __construct(int $courseSectionId)
    {
        $this->courseSectionId = $courseSectionId;
    }

    /**
     * Build an array that represents the Excel sheet.
     *
     * @return array
     */
    public function array(): array
    {
        // 1. Retrieve all sessions for the course section, ordered by session_start.
        $sessions = CourseSession::where('course_section_id', $this->courseSectionId)
            ->orderBy('session_start', 'asc')
            ->get();

        // 2. Retrieve all students that have attendance records for sessions in this course section.
        $studentIds = DB::table('attendances')
            ->join('course_sessions', 'attendances.course_session_id', '=', 'course_sessions.id')
            ->where('course_sessions.course_section_id', $this->courseSectionId)
            ->pluck('attendances.student_id')
            ->unique();
        $students = Student::whereIn('id', $studentIds)
            ->orderBy('last_name')
            ->get();

        // 3. Build header rows.
        // Header row 1: initially empty cells for fixed columns; then the month name for each session.
        // Header row 2: fixed columns then the day number for each session.
        $header1 = ['', '', ''];
        $header2 = ['Nb', 'ID', 'Name'];
        $this->monthColumns = []; // reset mapping

        // The first three columns (A, B, C) are fixed; session columns start at index 4.
        $columnIndex = 4; 
        foreach ($sessions as $session) {
            $date = Carbon::parse($session->session_start);
            $month = $date->format('F'); // e.g., "January"
            $day   = $date->format('j'); // e.g., "1", "15", etc.

            // Append month and day headers.
            $header1[] = $month;
            $header2[] = $day;

            // Record the column index under the appropriate month.
            if (!isset($this->monthColumns[$month])) {
                $this->monthColumns[$month] = [];
            }
            $this->monthColumns[$month][] = $columnIndex;

            $columnIndex++;
        }
        // Append the Sum column to both header rows.
        $header1[] = '';
        $header2[] = 'Sum';

        // 4. Build student data rows.
        $dataRows = [];
        $rowNumber = 1;
        foreach ($students as $student) {
            $row = [];
            $row[] = $rowNumber++; // Nb column.
            $row[] = $student->student_id; // Student ID.
            // Concatenate name in the format: first_name father_name last_name.
            $row[] = trim($student->first_name . ' ' . $student->father_name . ' ' . $student->last_name);
            
            // Initialize absence counter.
            $absenceCount = 0;
            // For each session, determine the attendance value.
            foreach ($sessions as $session) {
                $attendance = Attendance::where('course_session_id', $session->id)
                    ->where('student_id', $student->id)
                    ->first();
                $cellValue = '';
                if ($attendance) {
                    // If attendance is 'absent', add a "1" to the cell and increment the counter.
                    if ($attendance->value === 'abscent') {
                        $cellValue = '1';
                        $absenceCount++;
                    }
                }
                $row[] = $cellValue;
            }
            // Append the absence sum at the end of the row.
            $row[] = $absenceCount;
            $dataRows[] = $row;
        }

        // Merge header rows with data rows.
        return array_merge([$header1, $header2], $dataRows);
    }

    /**
     * Register events to style the sheet after it is created.
     *
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Determine the highest column and row.
                $highestColumn = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestRow();

                // Define header range for rows 1 and 2.
                $headerRange = 'A1:' . $highestColumn . '2';
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'BFBFBF'],
                    ],
                ]);

                // Merge cells for each month header in row 1.
                foreach ($this->monthColumns as $month => $columns) {
                    if (count($columns) > 0) {
                        $startIndex = min($columns);
                        $endIndex = max($columns);
                        $startColumn = Coordinate::stringFromColumnIndex($startIndex);
                        $endColumn = Coordinate::stringFromColumnIndex($endIndex);
                        $range = $startColumn . '1:' . $endColumn . '1';
                        $sheet->mergeCells($range);
                        // Center align the merged month header.
                        $sheet->getStyle($range)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    }
                }

                // Apply alignment for the day header row (row 2).
                $dayHeaderRange = 'A2:' . $highestColumn . '2';
                $sheet->getStyle($dayHeaderRange)->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($dayHeaderRange)->getAlignment()
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $sheet->getStyle($dayHeaderRange)->getAlignment()
                    ->setShrinkToFit(true);

                // Apply border styling to the entire table (headers and data).
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
