<?php

namespace App\Http\Controllers;

use App\DTOs\CourseEnrollment\CourseEnrollmentCreateDTO;
use App\DTOs\CourseEnrollment\CourseEnrollmentListDTO;
use App\DTOs\CourseEnrollment\CourseEnrollmentStudentDTO;
use App\DTOs\ErrorResponseDTO;
use App\DTOs\SuccessResponseDTO;
use App\Services\CourseEnrollmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CourseEnrollmentController extends Controller
{
    protected CourseEnrollmentService $service;

    public function __construct(CourseEnrollmentService $service)
    {
        $this->service = $service;
    }

    /**
     * Get all enrollments.
     */
    public function index(): JsonResponse
    {
        $enrollments = $this->service->getAllEnrollments();

        $dtos = $enrollments->map(function ($enrollment) {
            return CourseEnrollmentListDTO::fromModel($enrollment);
        });

        $responseDTO = new SuccessResponseDTO(200, 'Enrollments retrieved successfully', $dtos);
        return response()->json($responseDTO, 200);
    }

    /**
     * Get enrollments by course section ID.
     */
    public function byCourseSection(int $sectionId): JsonResponse
    {
        $enrollments = $this->service->getEnrollmentsBySectionId($sectionId);

        $dtos = $enrollments->map(function ($enrollment) {
            return CourseEnrollmentStudentDTO::fromModel($enrollment)->toArray();
        });

        $responseDTO = new SuccessResponseDTO(200, 'Enrollments retrieved successfully', $dtos);
        return response()->json($responseDTO, 200);
    }

    /**
     * Get enrollments by student ID.
     */
    public function byStudent(int $studentId): JsonResponse
    {
        $enrollments = $this->service->getEnrollmentsByStudentId($studentId);

        $dtos = $enrollments->map(function ($enrollment) {
            return CourseEnrollmentListDTO::fromModel($enrollment);
        });

        $responseDTO = new SuccessResponseDTO(200, 'Enrollments retrieved successfully', $dtos);
        return response()->json($responseDTO, 200);
    }

    /**
     * Create a new enrollment.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'course_section_id' => 'required|integer|exists:course_sections,id',
                'student_id' => 'required|integer|exists:students,id',
                'major_id' => 'required|integer|exists:majors,id',
                'status_id' => 'nullable|integer|exists:student_course_statuses,id',
            ]);

            $dto = CourseEnrollmentCreateDTO::fromRequest($request->all());

            $enrollment = $this->service->createEnrollment(
                $dto->courseSectionId,
                $dto->studentId,
                $dto->majorId,
                $dto->statusId
            );

            $responseData = CourseEnrollmentListDTO::fromModel($enrollment);
            $responseDTO = new SuccessResponseDTO(201, 'Enrollment created successfully', $responseData);
            return response()->json($responseDTO, 201);
        } catch (ValidationException $e) {
            $responseDTO = new ErrorResponseDTO(422, 'Validation failed', $e->errors());
            return response()->json($responseDTO, 422);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(400, $e->getMessage(), []);
            return response()->json($responseDTO, 400);
        }
    }

    /**
     * Update enrollment grades.
     */
    public function updateGrades(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'final_grade' => 'nullable|numeric|min:0|max:100',
                'letter_grade' => 'nullable|string|max:2',
            ]);

            $enrollment = $this->service->updateGrades(
                $id,
                $request->input('final_grade'),
                $request->input('letter_grade')
            );

            $responseData = CourseEnrollmentListDTO::fromModel($enrollment);
            $responseDTO = new SuccessResponseDTO(200, 'Grades updated successfully', $responseData);
            return response()->json($responseDTO, 200);
        } catch (ValidationException $e) {
            $responseDTO = new ErrorResponseDTO(422, 'Validation failed', $e->errors());
            return response()->json($responseDTO, 422);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(404, 'Enrollment not found', []);
            return response()->json($responseDTO, 404);
        }
    }

    /**
     * Update enrollment status.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'status_id' => 'required|integer|exists:student_course_statuses,id',
            ]);

            $enrollment = $this->service->updateEnrollment($id, [
                'status_id' => $request->input('status_id'),
            ]);

            $responseData = CourseEnrollmentListDTO::fromModel($enrollment);
            $responseDTO = new SuccessResponseDTO(200, 'Status updated successfully', $responseData);
            return response()->json($responseDTO, 200);
        } catch (ValidationException $e) {
            $responseDTO = new ErrorResponseDTO(422, 'Validation failed', $e->errors());
            return response()->json($responseDTO, 422);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(404, 'Enrollment not found', []);
            return response()->json($responseDTO, 404);
        }
    }

    /**
     * Delete an enrollment.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->deleteEnrollment($id);

            $responseDTO = new SuccessResponseDTO(200, 'Enrollment deleted successfully', []);
            return response()->json($responseDTO, 200);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(404, 'Enrollment not found', []);
            return response()->json($responseDTO, 404);
        }
    }
}
