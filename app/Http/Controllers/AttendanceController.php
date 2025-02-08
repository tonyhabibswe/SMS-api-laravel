<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Http\Requests\AttendanceUpdateRequest;
use Illuminate\Http\JsonResponse;

class AttendanceController extends Controller
{
    /**
     * Update the attendance value.
     *
     * @param  AttendanceUpdateRequest  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(AttendanceUpdateRequest $request, int $id): JsonResponse
    {
        // Find the attendance record by id
        $attendance = Attendance::find($id);
        
        if (!$attendance) {
            return response()->json(['message' => 'Attendance not found'], 404);
        }
        
        // Update the attendance value using validated data
        $attendance->value = $request->attendance;
        $attendance->save();
        
        return response()->json(['message' => 'Ok'], 200);
    }
}
