<?php

namespace App\Http\Controllers;

use App\DTOs\CoursePassingGrade\CoursePassingGradeCreateDTO;
use App\DTOs\CoursePassingGrade\CoursePassingGradeUpdateDTO;
use App\DTOs\ErrorResponseDTO;
use App\DTOs\SuccessResponseDTO;
use App\Http\Requests\CoursePassingGradeCreateRequest;
use App\Http\Requests\CoursePassingGradeUpdateRequest;
use App\Services\CoursePassingGradeService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Course Passing Grades",
 *     description="API endpoints for managing course passing grades"
 * )
 */
class CoursePassingGradeController extends Controller
{
    protected CoursePassingGradeService $coursePassingGradeService;

    public function __construct(CoursePassingGradeService $coursePassingGradeService)
    {
        $this->coursePassingGradeService = $coursePassingGradeService;
    }

    /**
     * @OA\Get(
     *     path="/api/course-passing-grades",
     *     summary="Get all course passing grades",
     *     tags={"Course Passing Grades"},
     *     @OA\Response(
     *         response=200,
     *         description="Course passing grades retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Course passing grades retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CoursePassingGradeList"))
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $passingGradesDTOs = $this->coursePassingGradeService->getAllCoursePassingGrades();
            $responseDTO = new SuccessResponseDTO(200, 'Course passing grades retrieved successfully', $passingGradesDTOs->toArray());

            return response()->json($responseDTO->toArray(), 200);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(500, 'Failed to retrieve course passing grades', [$e->getMessage()]);
            return response()->json($responseDTO->toArray(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/course-passing-grades",
     *     summary="Create a new course passing grade",
     *     tags={"Course Passing Grades"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="majorId", type="integer", example=1),
     *             @OA\Property(property="semesterId", type="integer", example=1),
     *             @OA\Property(property="courseId", type="integer", example=1),
     *             @OA\Property(property="gradeValue", type="number", format="float", example=75.5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Course passing grade created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=201),
     *             @OA\Property(property="message", type="string", example="Course passing grade created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/CoursePassingGradeDetail")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(CoursePassingGradeCreateRequest $request): JsonResponse
    {
        try {
            $dto = CoursePassingGradeCreateDTO::fromRequest($request->validated());
            $passingGradeDTO = $this->coursePassingGradeService->createCoursePassingGrade($dto);
            $responseDTO = new SuccessResponseDTO(201, 'Course passing grade created successfully', $passingGradeDTO->toArray());

            return response()->json($responseDTO->toArray(), 201);
        } catch (ValidationException $e) {
            $responseDTO = new ErrorResponseDTO(422, 'Validation failed', $e->errors());
            return response()->json($responseDTO->toArray(), 422);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(500, 'Failed to create course passing grade', [$e->getMessage()]);
            return response()->json($responseDTO->toArray(), 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/course-passing-grades/{id}",
     *     summary="Update a course passing grade",
     *     tags={"Course Passing Grades"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="majorId", type="integer", example=1),
     *             @OA\Property(property="semesterId", type="integer", example=1),
     *             @OA\Property(property="courseId", type="integer", example=1),
     *             @OA\Property(property="gradeValue", type="number", format="float", example=80.0)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Course passing grade updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Course passing grade updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/CoursePassingGradeDetail")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Course passing grade not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(CoursePassingGradeUpdateRequest $request, int $id): JsonResponse
    {
        try {
            $dto = CoursePassingGradeUpdateDTO::fromRequest($request->validated());
            $passingGradeDTO = $this->coursePassingGradeService->updateCoursePassingGrade($id, $dto);
            $responseDTO = new SuccessResponseDTO(200, 'Course passing grade updated successfully', $passingGradeDTO->toArray());

            return response()->json($responseDTO->toArray(), 200);
        } catch (ModelNotFoundException $e) {
            $responseDTO = new ErrorResponseDTO(404, 'Course passing grade not found', []);
            return response()->json($responseDTO->toArray(), 404);
        } catch (ValidationException $e) {
            $responseDTO = new ErrorResponseDTO(422, 'Validation failed', $e->errors());
            return response()->json($responseDTO->toArray(), 422);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(500, 'Failed to update course passing grade', [$e->getMessage()]);
            return response()->json($responseDTO->toArray(), 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/course-passing-grades/{id}",
     *     summary="Delete a course passing grade",
     *     tags={"Course Passing Grades"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Course passing grade deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Course passing grade deleted successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Course passing grade not found"
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->coursePassingGradeService->deleteCoursePassingGrade($id);

            if ($result) {
                $responseDTO = new SuccessResponseDTO(200, 'Course passing grade deleted successfully', []);
                return response()->json($responseDTO->toArray(), 200);
            }

            $responseDTO = new ErrorResponseDTO(500, 'Failed to delete course passing grade', []);
            return response()->json($responseDTO->toArray(), 500);
        } catch (ModelNotFoundException $e) {
            $responseDTO = new ErrorResponseDTO(404, 'Course passing grade not found', []);
            return response()->json($responseDTO->toArray(), 404);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(500, 'Failed to delete course passing grade', [$e->getMessage()]);
            return response()->json($responseDTO->toArray(), 500);
        }
    }

    /**
     * Get all passing grades by course ID.
     */
    public function getByCourse(int $courseId): JsonResponse
    {
        try {
            $passingGradesDTOs = $this->coursePassingGradeService->getCoursePassingGradesByCourse($courseId);
            $responseDTO = new SuccessResponseDTO(200, 'Course passing grades retrieved successfully', $passingGradesDTOs->toArray());

            return response()->json($responseDTO->toArray(), 200);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(500, 'Failed to retrieve course passing grades', [$e->getMessage()]);
            return response()->json($responseDTO->toArray(), 500);
        }
    }
}
