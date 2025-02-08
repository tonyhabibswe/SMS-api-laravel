<?php

namespace App\Http\Controllers;

use App\Http\Requests\SemesterCreateRequest;
use App\Http\Requests\SemesterEditRequest;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Semester;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    public function list(): JsonResponse
    {
        // Retrieve all semesters ordered by descending ID
        $semesters = Semester::orderByDesc('id')->get();

        // Return the result in a JSON response
        return response()->json($semesters, 200);
    }

    public function create(SemesterCreateRequest $request): JsonResponse
    {
        // Create a new Semester instance with validated data
        $semester = Semester::create([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        // Return a response with a 201 status and the created semester data
        return response()->json($semester, 201);
    }

    public function edit(SemesterEditRequest $request): JsonResponse
    {
        // Find the semester by ID
        $semester = Semester::find($request->id);
        
        // If semester not found, return a 404 error (This is handled by the `exists` rule in validation as well)
        if (!$semester) {
            return response()->json(['message' => 'Semester not found'], 404);
        }

        // Update the semester's name
        $semester->name = $request->name;

        // Save the updated semester
        $semester->save();

        // Return a success response
        return response()->json(['message' => 'Semester updated successfully'], 200);
    }

    public function delete(int $id): JsonResponse
    {
        // Find the semester by ID
        $semester = Semester::find($id);

        // Check if the semester exists
        if (!$semester) {
            return response()->json(['message' => 'Semester not found'], 404);
        }

        // Delete the semester
        $semester->delete();

        // Return a success response
        return response()->json(['message' => 'Semester deleted successfully'], 200);
    }

    public function listCoursesBySemesterId(int $id): JsonResponse
    {
        // Retrieve courses with semester_id = $semesterId and eager load the first session
        $courseSections = CourseSection::where('semester_id', $id)
                                        ->with([
                                            'firstSession',
                                            'course:code,name'  // Eager load only the specified columns from courses table
                                        ])
                                        ->get();

        // Optionally transform data if you only need specific fields
        $result = $courseSections->map(function ($courseSections) {
            return [
                'id' => $courseSections->id,
                'code' => $courseSections->course->code,
                'name' => $courseSections->course->name,
                'time' => $courseSections->time,
                'session' => $courseSections->firstSession ? [
                    'id' => $courseSections->firstSession->id,
                    'room' => $courseSections->firstSession->room,
                    'session_start' => $courseSections->firstSession->session_start,
                    'session_end' => $courseSections->firstSession->session_end,
                ] : null,
            ];
        });

        return response()->json($result, 200);
    }
}