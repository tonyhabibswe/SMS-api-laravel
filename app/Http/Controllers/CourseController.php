<?php

namespace App\Http\Controllers;

use App\DTOs\Course\CourseCreateDTO;
use App\DTOs\Course\CourseEditDTO;
use App\Http\Requests\CourseCreateRequest;
use App\Http\Requests\CourseEditRequest;
use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Http\JsonResponse;

class CourseController extends Controller
{
    protected CourseService $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    /**
     * List all courses.
     *
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $courseDTOs = $this->courseService->listCourses();
        return response()->json($courseDTOs, 200);
    }

    /**
     * Create a new course.
     *
     * @param CourseCreateRequest $request
     * @return JsonResponse
     */
    public function create(CourseCreateRequest $request): JsonResponse
    {
        // The request is already validated by CourseCreateRequest.
        $validatedData = $request->validated();

        // Build the DTO from the validated data.
        $dto = new CourseCreateDTO(
            $validatedData['code'],
            $validatedData['name']
        );

        // Use the service layer to create the course.
        $courseDTO = $this->courseService->createCourse($dto);

        // Return the created course DTO with a 201 status code.
        return response()->json($courseDTO, 201);
    }

    /**
     * Edit an existing course.
     *
     * @param CourseEditRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function edit(CourseEditRequest $request, int $id): JsonResponse
    {
        // Build the DTO from the validated request data and route parameter.
        $editDTO = new CourseEditDTO(
            $id,
            $request->input('code'),
            $request->input('name')
        );
        
        // Call the service to update the course.
        $updatedCourseDTO = $this->courseService->updateCourse($editDTO);
        
        if (!$updatedCourseDTO) {
            return response()->json(['message' => 'Course not found'], 404);
        }
        
        return response()->json($updatedCourseDTO, 200);
    }

    /**
     * Delete a course.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        $deleted = $this->courseService->deleteCourse($id);

        if (!$deleted) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        return response()->json(['message' => 'Course deleted successfully'], 200);
    }
}
