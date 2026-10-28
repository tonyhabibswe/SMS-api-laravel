<?php

namespace App\Http\Controllers;

use App\DTOs\SuccessResponseDTO;
use App\Services\StudentService;
use Illuminate\Http\JsonResponse;

class StudentController extends Controller
{
    protected StudentService $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    /**
     * List the students who took at least one course section, with how many
     * course sections and semesters they had and their latest semester.
     *
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        try {
            $students = $this->studentService->listStudents();
        } catch (\Exception $e) {
            return $this->exceptionResponse($e, 'Failed to retrieve students');
        }

        $responseDTO = new SuccessResponseDTO(200, 'Students retrieved successfully', $students);
        return response()->json($responseDTO, 200);
    }

    /**
     * Get a student and the course sections they took, newest semester first,
     * with their status, grade and attendance in each.
     *
     * @param int $id The students.id primary key.
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $student = $this->studentService->getStudentHistory($id);
        } catch (\Exception $e) {
            return $this->exceptionResponse($e, 'Failed to retrieve student');
        }

        $responseDTO = new SuccessResponseDTO(200, 'Student retrieved successfully', $student);
        return response()->json($responseDTO, 200);
    }
}
