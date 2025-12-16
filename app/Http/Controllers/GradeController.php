<?php

namespace App\Http\Controllers;

use App\DTOs\ErrorResponseDTO;
use App\DTOs\SuccessResponseDTO;
use App\Http\Requests\BulkGradeUpdateRequest;
use App\Http\Requests\GradeUpdateRequest;
use App\Services\GradeService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Grades",
 *     description="API endpoints for managing individual student grades"
 * )
 */
class GradeController extends Controller
{
    protected GradeService $service;

    public function __construct(GradeService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/api/grades/gradeable-item/{gradeableItemId}",
     *     summary="Get all grades for a specific gradeable item",
     *     tags={"Grades"},
     *     @OA\Parameter(
     *         name="gradeableItemId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Grades retrieved successfully"
     *     )
     * )
     */
    public function getByGradeableItem(int $gradeableItemId): JsonResponse
    {
        try {
            $grades = $this->service->getGradesByGradeableItem($gradeableItemId);
            $responseDTO = new SuccessResponseDTO(200, 'Grades retrieved successfully', $grades->map->toArray()->toArray());
            return response()->json($responseDTO->toArray(), 200);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(500, 'Failed to retrieve grades', [$e->getMessage()]);
            return response()->json($responseDTO->toArray(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/grades/enrollment/{enrollmentId}",
     *     summary="Get all grades for a specific enrollment",
     *     tags={"Grades"},
     *     @OA\Parameter(
     *         name="enrollmentId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Grades retrieved successfully"
     *     )
     * )
     */
    public function getByEnrollment(int $enrollmentId): JsonResponse
    {
        try {
            $grades = $this->service->getGradesByEnrollment($enrollmentId);
            $responseDTO = new SuccessResponseDTO(200, 'Grades retrieved successfully', $grades->map->toArray()->toArray());
            return response()->json($responseDTO->toArray(), 200);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(500, 'Failed to retrieve grades', [$e->getMessage()]);
            return response()->json($responseDTO->toArray(), 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/grades/{id}",
     *     summary="Update a single grade value",
     *     tags={"Grades"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="gradeValue", type="number", format="float", example=85.50)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Grade updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Grade not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(GradeUpdateRequest $request, int $id): JsonResponse
    {
        try {
            $grade = $this->service->updateGrade($id, $request->validated()['gradeValue']);
            $responseDTO = new SuccessResponseDTO(200, 'Grade updated successfully', $grade->toArray());
            return response()->json($responseDTO->toArray(), 200);
        } catch (ModelNotFoundException $e) {
            $responseDTO = new ErrorResponseDTO(404, 'Grade not found', [$e->getMessage()]);
            return response()->json($responseDTO->toArray(), 404);
        } catch (ValidationException $e) {
            $responseDTO = new ErrorResponseDTO(422, 'Validation error', $e->errors());
            return response()->json($responseDTO->toArray(), 422);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(500, 'Failed to update grade', [$e->getMessage()]);
            return response()->json($responseDTO->toArray(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/grades/bulk-update",
     *     summary="Update multiple grades at once",
     *     tags={"Grades"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="grades",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="gradeValue", type="number", format="float", example=85.50)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Grades updated successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function bulkUpdate(BulkGradeUpdateRequest $request): JsonResponse
    {
        try {
            $grades = $this->service->bulkUpdateGrades($request->validated()['grades']);
            $responseDTO = new SuccessResponseDTO(200, 'Grades updated successfully', $grades->map->toArray()->toArray());
            return response()->json($responseDTO->toArray(), 200);
        } catch (ValidationException $e) {
            $responseDTO = new ErrorResponseDTO(422, 'Validation error', $e->errors());
            return response()->json($responseDTO->toArray(), 422);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(500, 'Failed to update grades', [$e->getMessage()]);
            return response()->json($responseDTO->toArray(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/grades/final/{enrollmentId}/{courseSectionId}",
     *     summary="Calculate final grade for an enrollment based on weighted categories",
     *     tags={"Grades"},
     *     @OA\Parameter(
     *         name="enrollmentId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="courseSectionId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Final grade calculated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="statusCode", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Final grade calculated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="enrollmentId", type="integer", example=1),
     *                 @OA\Property(property="courseSectionId", type="integer", example=1),
     *                 @OA\Property(property="finalGrade", type="number", format="float", example=87.50)
     *             )
     *         )
     *     )
     * )
     */
    public function getFinalGrade(int $enrollmentId, int $courseSectionId): JsonResponse
    {
        try {
            $finalGrade = $this->service->calculateFinalGrade($enrollmentId, $courseSectionId);

            $data = [
                'enrollmentId' => $enrollmentId,
                'courseSectionId' => $courseSectionId,
                'finalGrade' => $finalGrade,
            ];

            $responseDTO = new SuccessResponseDTO(200, 'Final grade calculated successfully', $data);
            return response()->json($responseDTO->toArray(), 200);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(500, 'Failed to calculate final grade', [$e->getMessage()]);
            return response()->json($responseDTO->toArray(), 500);
        }
    }
}
