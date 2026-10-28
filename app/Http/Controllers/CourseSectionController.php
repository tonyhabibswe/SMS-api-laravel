<?php

namespace App\Http\Controllers;

use App\DTOs\CourseSection\CourseSectionCreateDTO;
use App\DTOs\CourseSection\CourseSectionEditDTO;
use App\DTOs\ErrorResponseDTO;
use App\DTOs\Session\SessionCreateDTO;
use App\DTOs\Student\StudentCreateDTO;
use App\DTOs\SuccessResponseDTO;
use App\Http\Requests\CourseSectionCreateRequest;
use App\Http\Requests\CourseSectionEditRequest;
use App\Http\Requests\ImportStudentsRequest;
use App\Http\Requests\SessionCreateRequest;
use App\Http\Requests\StudentCreateRequest;
use App\Http\Requests\CourseSection\GetGradesTableRequest;
use App\Http\Requests\CourseSection\PreviewGradesTableRequest;
use App\Services\CourseSectionService;
use App\Services\CourseSessionService;
use App\Services\StudentAttendanceService;
use App\Services\StudentImportService;
use App\Services\GradeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class CourseSectionController extends Controller
{
    protected CourseSessionService $courseSessionService;
    protected CourseSectionService $courseSectionService;
    protected StudentAttendanceService $studentAttendanceService;
    protected StudentImportService $studentImportService;
    protected GradeService $gradeService;

    public function __construct(
        CourseSectionService $courseSectionService,
        StudentAttendanceService $studentAttendanceService,
        StudentImportService $studentImportService,
        CourseSessionService $courseSessionService,
        GradeService $gradeService
    ) {
        $this->courseSessionService = $courseSessionService;
        $this->courseSectionService = $courseSectionService;
        $this->studentAttendanceService = $studentAttendanceService;
        $this->studentImportService = $studentImportService;
        $this->gradeService = $gradeService;
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
            $request->semesterId,
            $request->courseId,
            $request->sectionCode,
            $request->courseDays,
            $request->startSessionTime,
            $request->endSessionTime,
            $request->room,
            $request->curveAlgorithm
        );

        try {
            $courseSection = $this->courseSectionService->createCourseSection($dto);
        } catch (\Exception $e) {
            return $this->exceptionResponse($e, 'Failed to create course section');
        }
        $responseDTO = new SuccessResponseDTO(201, 'Course section created successfully', $courseSection);
        return response()->json($responseDTO, 201);
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
            $request->sectionCode,
            $request->curveAlgorithm,
            $request->has('curveAlgorithm')
        );

        // Call the service layer to update the course section.
        $updatedCourseSectionDTO = $this->courseSectionService->updateCourseSection($editDTO);
        if (!$updatedCourseSectionDTO) {
            $responseDTO = new ErrorResponseDTO(404, "Course section not found", []);
            return response()->json($responseDTO, 404);
        }

        $responseDTO = new SuccessResponseDTO(200, 'Course section edited successfully', $updatedCourseSectionDTO);
        return response()->json($responseDTO, 200);
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
            $responseDTO = new ErrorResponseDTO(404, "Course section not found", []);
            return response()->json($responseDTO, 404);
        }

        $responseDTO = new SuccessResponseDTO(200, 'Course section deleted successfully', []);
        return response()->json($responseDTO, 200);
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
            $data['studentId'],
            $data['firstName'],
            $data['fatherName'],
            $data['lastName'],
            $data['major'],
            $data['email'],
            $data['campus']
        );

        try {
            $student = $this->studentAttendanceService->createStudentWithAttendances($dto, $id);
        } catch (\Exception $e) {
            return $this->exceptionResponse($e, 'Failed to create student');
        }

        $responseDTO = new SuccessResponseDTO(201, 'Student created successfully', $student);
        return response()->json($responseDTO, 201);
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
            return $this->exceptionResponse($e, 'Failed to import students');
        }

        $responseDTO = new SuccessResponseDTO(200, 'Students imported and attendance records created successfully', []);
        return response()->json($responseDTO, 200);
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
            $data['sessionStart'],
            $data['sessionEnd']
        );

        try {
            $session = $this->courseSessionService->createSessionForCourseSection($dto, $id);
        } catch (ModelNotFoundException $e) {
            $responseDTO = new ErrorResponseDTO(404, 'Course section not found', []);
            return response()->json($responseDTO, 404);
        } catch (\Exception $e) {
            return $this->exceptionResponse($e, 'Failed to create session');
        }

        $responseDTO = new SuccessResponseDTO(201, 'Session created successfully', $session);
        // Return the new session with a 201 (Created) status code.
        return response()->json($responseDTO, 201);
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
        $responseDTO = new SuccessResponseDTO(200, 'Sessions retrieved successfully', $sessionDTOs);
        return response()->json($responseDTO, 200);
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
        $reponseDTO = new SuccessResponseDTO(200, 'Student attendance summary retrieved successfully', $attendanceDTOs);
        return response()->json($reponseDTO, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/course-sections/{courseSectionId}/grades/table",
     *     summary="Get grades table for course section",
     *     tags={"Course Sections", "Grades"},
     *     @OA\Parameter(
     *         name="courseSectionId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Grades table retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="statusCode", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Grades table retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="courseSectionId", type="integer", example=1),
     *                 @OA\Property(property="courseName", type="string", example="CSC 210 - Data Structures"),
     *                 @OA\Property(property="semesterName", type="string", example="Fall 2023"),
     *                 @OA\Property(property="totalStudents", type="integer", example=25),
     *                 @OA\Property(property="columns", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="rows", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Course section not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function getGradesTable(GetGradesTableRequest $request, int $courseSectionId): JsonResponse
    {
        try {
            $gradesTableDTO = $this->gradeService->getGradesTableForCourseSection(
                $courseSectionId
            );

            $responseDTO = new SuccessResponseDTO(
                200,
                'Grades table retrieved successfully',
                $gradesTableDTO->toArray()
            );

            return response()->json($responseDTO->toArray(), 200);
        } catch (ModelNotFoundException $e) {
            $responseDTO = new ErrorResponseDTO(404, 'Course section not found', []);
            return response()->json($responseDTO->toArray(), 404);
        } catch (\Exception $e) {
            return $this->exceptionResponse($e, 'Failed to retrieve grades table');
        }
    }

    /**
     * @OA\Post(
     *     path="/api/course-sections/{courseSectionId}/grades/preview",
     *     summary="Preview the grades table with some grades changed, saving nothing",
     *     description="Returns the grades table calculated as if the given grades replaced the saved ones. With neededItemId, each row's needed says the lowest grade on that item that reaches the student's passing mark (their major's passing grade for the course and semester, otherwise 60), compared with the curved grade.",
     *     tags={"Course Sections", "Grades"},
     *     @OA\Parameter(
     *         name="courseSectionId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="grades",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="enrollmentId", type="integer", example=12),
     *                     @OA\Property(property="gradeableItemId", type="integer", example=3),
     *                     @OA\Property(property="gradeValue", type="number", nullable=true, example=70)
     *                 )
     *             ),
     *             @OA\Property(property="neededItemId", type="integer", nullable=true, example=3)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Grades preview calculated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="statusCode", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Grades preview calculated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="The same shape as the grades table. Each row also has needed: {status: passing|needed|cannot, value, best}, or null for W, UW and I.",
     *                 @OA\Property(property="columns", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="rows", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Course section not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="A grade or the item isn't in the course section, or a grade is out of range"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function previewGradesTable(PreviewGradesTableRequest $request, int $courseSectionId): JsonResponse
    {
        try {
            $validated = $request->validated();
            $gradesTableDTO = $this->gradeService->previewGradesTable(
                $courseSectionId,
                $validated['grades'] ?? [],
                $validated['neededItemId'] ?? null
            );

            $responseDTO = new SuccessResponseDTO(
                200,
                'Grades preview calculated successfully',
                $gradesTableDTO->toArray()
            );

            return response()->json($responseDTO->toArray(), 200);
        } catch (ValidationException $e) {
            $responseDTO = new ErrorResponseDTO(422, 'Validation error', $e->errors());
            return response()->json($responseDTO->toArray(), 422);
        } catch (ModelNotFoundException $e) {
            $responseDTO = new ErrorResponseDTO(404, 'Course section not found', []);
            return response()->json($responseDTO->toArray(), 404);
        } catch (\Exception $e) {
            return $this->exceptionResponse($e, 'Failed to calculate the grades preview');
        }
    }

    /**
     * @OA\Get(
     *     path="/api/course-sections/{courseSectionId}/grades/export",
     *     summary="Export grades and attendance to Excel",
     *     tags={"Course Sections", "Grades"},
     *     @OA\Parameter(
     *         name="courseSectionId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Grades and attendance exported successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="statusCode", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Grades and attendance exported successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="fileName", type="string", example="CSC 210 - A - FALL 2025.xlsx"),
     *                 @OA\Property(property="file", type="string", example="UEsDBBQABgAI...")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Course section not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function exportGradesTable(int $courseSectionId): JsonResponse
    {
        try {
            $exportData = $this->gradeService->exportGradesTable($courseSectionId);

            $responseDTO = new SuccessResponseDTO(
                200,
                'Grades and attendance exported successfully',
                $exportData
            );

            return response()->json($responseDTO->toArray(), 200);
        } catch (ModelNotFoundException $e) {
            $responseDTO = new ErrorResponseDTO(404, 'Course section not found', []);
            return response()->json($responseDTO->toArray(), 404);
        } catch (\Exception $e) {
            return $this->exceptionResponse($e, 'Failed to export grades and attendance');
        }
    }
}
