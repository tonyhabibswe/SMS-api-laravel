<?php

namespace App\Services;

use App\DTOs\Student\StudentCreateDTO;
use App\Repositories\StudentRepository;
use App\Repositories\CourseSessionRepository;
use App\Repositories\AttendanceRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class StudentImportService
{
    protected StudentRepository $studentRepository;
    protected CourseSessionRepository $courseSessionRepository;
    protected AttendanceRepository $attendanceRepository;

    public function __construct(
        StudentRepository $studentRepository,
        CourseSessionRepository $courseSessionRepository,
        AttendanceRepository $attendanceRepository
    ) {
        $this->studentRepository         = $studentRepository;
        $this->courseSessionRepository   = $courseSessionRepository;
        $this->attendanceRepository      = $attendanceRepository;
    }

    /**
     * Import students from a CSV file and create attendance records for each session.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param int $courseSectionId
     * @throws Exception
     */
    public function importStudents($file, int $courseSectionId): void
    {
        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            throw new Exception('Unable to open the file.', 500);
        }
        
        // Skip the first 4 lines (header or irrelevant)
        for ($i = 0; $i < 4; $i++) {
            fgetcsv($handle);
        }
        
        $csvStudents = [];
        while (($row = fgetcsv($handle)) !== false) {
            // Ensure that we have enough columns (expecting 7 columns)
            if (count($row) < 7) {
                continue;
            }
            // Create a DTO for each row.
            $csvStudents[] = new StudentCreateDTO(
                $row[0],
                $row[1],
                $row[2],
                $row[3],
                $row[4],
                $row[5],
                $row[6]
            );
        }
        fclose($handle);
        
        if (empty($csvStudents)) {
            throw new Exception('No student records found in CSV.', 400);
        }
        
        DB::transaction(function () use ($csvStudents, $courseSectionId) {
            // Extract unique student_ids from DTOs.
            $recordIds = collect($csvStudents)
                ->pluck('studentId')
                ->unique()
                ->toArray();

            // Retrieve existing students.
            $existingStudents = $this->studentRepository->getStudentsByStudentIds($recordIds);
            $existingStudentIds = $existingStudents->pluck('studentId')->toArray();

            // Filter out new student DTOs (those that don't exist already).
            $newStudentDTOs = collect($csvStudents)
                ->filter(function (StudentCreateDTO $dto) use ($existingStudentIds) {
                    return !in_array($dto->studentId, $existingStudentIds);
                })
                ->values();

            // Create new students.
            $newStudents = $newStudentDTOs->map(function (StudentCreateDTO $dto) {
                return $this->studentRepository->findOrCreateStudent($dto);
            });

            // Merge existing and new students.
            $allStudents = $existingStudents->merge($newStudents);

            // Retrieve sessions for the given course section.
            $sessions = $this->courseSessionRepository->getSessionsByCourseSectionId($courseSectionId);
            if ($sessions->isEmpty()) {
                throw new Exception("This course section doesn't have any session created", 400);
            }

            // For each student, check if attendance exists for the first session.
            $firstSession = $sessions->first();
            foreach ($allStudents as $student) {
                $attendanceInDb = $this->attendanceRepository->findAttendance($firstSession->id, $student->id);
                if (!$attendanceInDb) {
                    // Create attendance records for each session.
                    $this->attendanceRepository->createAttendancesForStudent($sessions, $student->id);
                }
            }
        });
    }
}
