<?php

namespace App\Http\Controllers;

use App\DTOs\CourseSection\CourseSectionCreateDTO;
use App\DTOs\CourseSection\CourseSectionEditDTO;
use App\DTOs\Session\SessionCreateDTO;
use App\DTOs\Student\StudentCreateDTO;
use App\Http\Requests\CourseSectionCreateRequest;
use App\Http\Requests\CourseSectionEditRequest;
use App\Http\Requests\ImportStudentsRequest;
use App\Http\Requests\SessionCreateRequest;
use App\Http\Requests\StudentCreateRequest;
use App\Models\Attendance;
use App\Models\CourseSection;
use App\Models\CourseSession;
use App\Models\Student;
use App\Services\CourseSectionService;
use App\Services\CourseSessionService;
use App\Services\StudentAttendanceService;
use App\Services\StudentImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CourseSectionController extends Controller
{
    protected CourseSessionService $courseSessionService;
    protected CourseSectionService $courseSectionService;
    protected StudentAttendanceService $studentAttendanceService;
    protected StudentImportService $studentImportService;

    public function __construct(
        CourseSectionService $courseSectionService, 
        StudentAttendanceService $studentAttendanceService, 
        StudentImportService $studentImportService,
        CourseSessionService $courseSessionService)
    {
        $this->courseSessionService = $courseSessionService;
        $this->courseSectionService = $courseSectionService;
        $this->studentAttendanceService = $studentAttendanceService;
        $this->studentImportService = $studentImportService;
    }


    /**
     * Create a new CourseSection with generated sessions.
     *
     * Route: POST /course-section (or similar)
     *
     * @param CourseSectionCreateRequest $request
     * @return JsonResponse
     */
    public function create(CourseSectionCreateRequest $request): JsonResponse
    {
        // Build the DTO from validated request data.
        $dto = new CourseSectionCreateDTO(
            $request->semester_id,
            $request->course_id,
            $request->section_code,
            $request->course_days,
            $request->start_session_time,
            $request->end_session_time,
            $request->room
        );

        try {
            $courseSection = $this->courseSectionService->createCourseSection($dto);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        return response()->json($courseSection, 201);
    }

    /**
     * Edit an existing course section.
     *
     * @param int $id
     * @param CourseSectionEditRequest $request
     * @return JsonResponse
     */
    public function edit(int $id, CourseSectionEditRequest $request): JsonResponse
    {
        // Build the DTO from the validated request data and route parameter.
        $editDTO = new CourseSectionEditDTO(
            $id,
            $request->section_code
        );

        // Call the service layer to update the course section.
        $updatedCourseSectionDTO = $this->courseSectionService->updateCourseSection($editDTO);

        if (!$updatedCourseSectionDTO) {
            return response()->json(['message' => 'Course section not found'], 404);
        }

        return response()->json($updatedCourseSectionDTO, 200);
    }

    /**
     * Delete a course section.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        $deleted = $this->courseSectionService->deleteCourseSection($id);

        if (!$deleted) {
            return response()->json(['message' => 'Course section not found'], 404);
        }

        return response()->json(['message' => 'Course section deleted successfully'], 200);
    }


    /**
     * Create a student and add attendances for all sessions of the course section.
     *
     * Route: POST /course-section/{id}/student
     *
     * @param  StudentCreateRequest  $request
     * @param  int  $id  The course_section id.
     * @return JsonResponse
     */
    public function createStudent(StudentCreateRequest $request, int $id): JsonResponse
    {
        // Retrieve and validate the student data.
        $data = $request->validated();

        // Build the DTO from the request data.
        $dto = new StudentCreateDTO(
            $data['student_id'],
            $data['first_name'],
            $data['father_name'],
            $data['last_name'],
            $data['major'],
            $data['email'],
            $data['campus']
        );

        try {
            $student = $this->studentAttendanceService->createStudentWithAttendances($dto, $id);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        return response()->json($student, 201);
    }


    /**
     * Import students from CSV and create attendance records for all sessions of the course section.
     *
     * Route: POST /course-section/{id}/import-students
     *
     * @param ImportStudentsRequest $request
     * @param int $id The course_section id.
     * @return JsonResponse
     */
    public function importStudents(ImportStudentsRequest $request, int $id): JsonResponse
    {
        try {
            $this->studentImportService->importStudents($request->file('file'), $id);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
        
        return response()->json(['message' => 'Students imported and attendance records created successfully.'], 200);
    }


    /**
     * Create a new session for a course section and generate attendance records.
     *
     * @param SessionCreateRequest $request
     * @param int $id The course_section id.
     * @return JsonResponse
     */
    public function createSession(SessionCreateRequest $request, int $id): JsonResponse
    {
        // The request has been validated by SessionCreateRequest.
        $data = $request->validated();
        
        // Build the DTO from the validated request data.
        $dto = new SessionCreateDTO(
            $data['room'],
            $data['session_start'],
            $data['session_end']
        );

        try {
            $session = $this->courseSessionService->createSessionForCourseSection($dto, $id);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
        
        // Return the new session with a 201 (Created) status code.
        return response()->json($session, 201);
    }

    /**
     * Get all sessions until today's date for a given course section.
     *
     * @param int $id The course_section id.
     * @return JsonResponse
     */
    public function getSessions(int $id): JsonResponse
    {
        $sessionDTOs = $this->courseSessionService->getSessionsUntilToday($id);
        return response()->json($sessionDTOs, 200);
    }

    /**
     * Get student attendance summary for a given course section.
     *
     * @param int $id The course_section id.
     * @return JsonResponse
     */
    public function getStudents(int $id): JsonResponse
    {
        $attendanceDTOs = $this->studentAttendanceService->getStudentAttendanceSummary($id);
        return response()->json($attendanceDTOs, 200);
    }

}
