<?php

namespace App\Http\Controllers;

use App\DTOs\Attendance\AttendanceUpdateBulkDTO;
use App\DTOs\Attendance\AttendanceUpdateDTO;
use App\DTOs\ErrorResponseDTO;
use App\DTOs\SuccessResponseDTO;
use App\Http\Requests\AttendanceBulkUpdateRequest;
use App\Http\Requests\AttendanceUpdateRequest;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;

class AttendanceController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }


    /**
     * Get all students that should a attend a course session.
     *
     * @param int $id
     * @return JsonResponse
     */

    public function listStudentsAttendance(int $id): JsonResponse
    {
        $attendanceDTOs = $this->attendanceService->listStudentsAttendance($id);
        $responseDTO = new SuccessResponseDTO(200, 'Operation successful', $attendanceDTOs);
        return response()->json($responseDTO, $responseDTO->statusCode);
    }

    /**
     * Update the attendance value.
     *
     * @param AttendanceUpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(AttendanceUpdateRequest $request, int $id): JsonResponse
    {
        // Build the DTO using the validated request data and the route parameter.
        $dto = new AttendanceUpdateDTO($id, $request->attendance);
        
        // Attempt to update the attendance.
        $updated = $this->attendanceService->updateAttendance($dto);
        if (!$updated) {
            $responseDTO = new ErrorResponseDTO(404, 'Attendance not found', []);
            return response()->json($responseDTO, $responseDTO->statusCode);
        }
        
        $responseDTO = new SuccessResponseDTO(200, 'Attendance updated successfully', []);
        return response()->json($responseDTO, $responseDTO->statusCode);
    }
    
    /**
     * Update attendance values for a given session.
     *
     *
     * @param AttendanceUpdateValuesRequest $request
     * @param int $id Session id.
     * @return JsonResponse
     */
    public function updateBulkAttendanceValues(AttendanceBulkUpdateRequest $request, int $id): JsonResponse
    {
        // Build the DTO from the validated request data.
        $dto = new AttendanceUpdateBulkDTO(
            $request->attendanceIds,
            $request->attendance
        );

        try {
            $this->attendanceService->updateBulkAttendanceValues($id, $dto);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO($e->getCode() ?: 400, $e->getMessage(), []);
            return response()->json($responseDTO, $responseDTO->statusCode);
        }

        $responseDTO = new SuccessResponseDTO(200, 'Attendances updated successfully', []);
        return response()->json($responseDTO, $responseDTO->statusCode);
    }

    /**
     * Update all attendance values for a given session.
     *
     * @param AttendanceUpdateRequest $request
     * @param int $id Session id.
     * @return JsonResponse
     */
    public function updateAllAttendanceValues(AttendanceUpdateRequest $request, int $id): JsonResponse
    {
        $dto = new AttendanceUpdateDTO($id, $request->attendance);

        try {
            $this->attendanceService->updateAllAttendanceValues($id, $dto);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO($e->getCode() ?: 400, $e->getMessage(), []);
            return response()->json($responseDTO , $responseDTO->statusCode);
        }

        $responseDTO = new SuccessResponseDTO(200, 'Attendances updated successfully', []);
        return response()->json($responseDTO, $responseDTO->statusCode);
    }
}
