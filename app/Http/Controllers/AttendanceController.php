<?php

namespace App\Http\Controllers;

use App\DTOs\Attendance\AttendanceUpdateDTO;
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
            return response()->json(['message' => 'Attendance not found'], 404);
        }
        
        return response()->json(['message' => 'Ok'], 200);
    }
}
