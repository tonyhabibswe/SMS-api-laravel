<?php

namespace App\Http\Controllers;

use App\DTOs\ErrorResponseDTO;
use App\DTOs\SuccessResponseDTO;
use App\Services\StudentMajorHistoryService;
use Illuminate\Http\JsonResponse;

class StudentMajorHistoryController extends Controller
{
    protected StudentMajorHistoryService $majorHistoryService;

    public function __construct(StudentMajorHistoryService $majorHistoryService)
    {
        $this->majorHistoryService = $majorHistoryService;
    }

    /**
     * Get student major history by student ID.
     *
     * @param int $studentId
     * @return JsonResponse
     */
    public function getStudentHistory(int $studentId): JsonResponse
    {
        $historyDTOs = $this->majorHistoryService->getStudentMajorHistory($studentId);
        $responseDTO = new SuccessResponseDTO(200, 'Student major history retrieved successfully', $historyDTOs);

        return response()->json($responseDTO, 200);
    }

    /**
     * Get current major for a student.
     *
     * @param int $studentId
     * @return JsonResponse
     */
    public function getCurrentStudentMajor(int $studentId): JsonResponse
    {
        $currentMajorDTO = $this->majorHistoryService->getCurrentStudentMajor($studentId);

        if (!$currentMajorDTO) {
            $responseDTO = new ErrorResponseDTO(404, "No major history found for student", []);
            return response()->json($responseDTO, 404);
        }

        $responseDTO = new SuccessResponseDTO(200, 'Current student major retrieved successfully', $currentMajorDTO);
        return response()->json($responseDTO, 200);
    }

    /**
     * Get major history for a semester.
     *
     * @param int $semesterId
     * @return JsonResponse
     */
    public function getSemesterHistory(int $semesterId): JsonResponse
    {
        $historyDTOs = $this->majorHistoryService->getMajorHistoryBySemester($semesterId);
        $responseDTO = new SuccessResponseDTO(200, 'Semester major history retrieved successfully', $historyDTOs);

        return response()->json($responseDTO, 200);
    }
}
