<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseCreateRequest;
use App\Http\Requests\CourseEditRequest;
use App\Models\Course;
use Illuminate\Http\JsonResponse;

class CourseController extends Controller
{
    /**
     * List all courses.
     */
    public function list(): JsonResponse
    {
        $courses = Course::orderBy('id')->get();
        return response()->json($courses, 200);
    }

    /**
     * Create a new course.
     */
    public function create(CourseCreateRequest $request): JsonResponse
    {
        // The request is already validated via CourseCreateRequest.
        $validatedData = $request->validated();

        // Create the course.
        $course = Course::create($validatedData);

        return response()->json($course, 201);
    }

    /**
     * Edit an existing course.
     */
    public function edit(CourseEditRequest $request, int $id): JsonResponse
    {
        // Find the course by its id.
        $course = Course::find($id);
        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        // Validate the request and update the course.
        $validatedData = $request->validated();
        $course->update($validatedData);

        return response()->json($course, 200);
    }

    /**
     * Delete a course.
     */
    public function delete(int $id): JsonResponse
    {
        // Find the course record.
        $course = Course::find($id);
        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        // Delete the course.
        $course->delete();

        return response()->json(['message' => 'Course deleted successfully'], 200);
    }
}
