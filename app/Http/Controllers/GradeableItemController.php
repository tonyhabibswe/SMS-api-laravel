<?php

namespace App\Http\Controllers;

use App\DTOs\GradeableItem\GradeableItemCreateDTO;
use App\DTOs\GradeableItem\GradeableItemUpdateDTO;
use App\DTOs\ErrorResponseDTO;
use App\DTOs\SuccessResponseDTO;
use App\Http\Requests\GradeableItemCreateRequest;
use App\Http\Requests\GradeableItemUpdateRequest;
use App\Services\GradeableItemService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Gradeable Items",
 *     description="API endpoints for managing gradeable items (assignments, exams, etc.)"
 * )
 */
class GradeableItemController extends Controller
{
    protected GradeableItemService $service;

    public function __construct(GradeableItemService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/api/gradeable-items",
     *     summary="Get all gradeable items",
     *     tags={"Gradeable Items"},
     *     @OA\Response(
     *         response=200,
     *         description="Gradeable items retrieved successfully"
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $items = $this->service->getAllItems();
            $responseDTO = new SuccessResponseDTO(200, 'Gradeable items retrieved successfully', $items->map->toArray()->toArray());
            return response()->json($responseDTO->toArray(), 200);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(500, 'Failed to retrieve gradeable items', [$e->getMessage()]);
            return response()->json($responseDTO->toArray(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/gradeable-items/category/{categoryId}",
     *     summary="Get all items for a category",
     *     tags={"Gradeable Items"},
     *     @OA\Parameter(
     *         name="categoryId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Items retrieved successfully"
     *     )
     * )
     */
    public function getByCategory(int $categoryId): JsonResponse
    {
        try {
            $items = $this->service->getItemsByCategory($categoryId);
            $responseDTO = new SuccessResponseDTO(200, 'Items retrieved successfully', $items->map->toArray()->toArray());
            return response()->json($responseDTO->toArray(), 200);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(500, 'Failed to retrieve items', [$e->getMessage()]);
            return response()->json($responseDTO->toArray(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/gradeable-items/course-section/{courseSectionId}",
     *     summary="Get all items for a course section",
     *     tags={"Gradeable Items"},
     *     @OA\Parameter(
     *         name="courseSectionId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Items retrieved successfully"
     *     )
     * )
     */
    public function getByCourseSection(int $courseSectionId): JsonResponse
    {
        try {
            $items = $this->service->getItemsByCourseSection($courseSectionId);
            $responseDTO = new SuccessResponseDTO(200, 'Items retrieved successfully', $items->map->toArray()->toArray());
            return response()->json($responseDTO->toArray(), 200);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(500, 'Failed to retrieve items', [$e->getMessage()]);
            return response()->json($responseDTO->toArray(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/gradeable-items/{id}",
     *     summary="Get a specific gradeable item",
     *     tags={"Gradeable Items"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item retrieved successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Item not found"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $item = $this->service->getItemById($id);
            $responseDTO = new SuccessResponseDTO(200, 'Item retrieved successfully', $item->toArray());
            return response()->json($responseDTO->toArray(), 200);
        } catch (ModelNotFoundException $e) {
            $responseDTO = new ErrorResponseDTO(404, 'Item not found', [$e->getMessage()]);
            return response()->json($responseDTO->toArray(), 404);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(500, 'Failed to retrieve item', [$e->getMessage()]);
            return response()->json($responseDTO->toArray(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/gradeable-items",
     *     summary="Create a new gradeable item (automatically creates grades for all enrolled students)",
     *     tags={"Gradeable Items"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="categoryId", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Midterm Exam"),
     *             @OA\Property(property="maxPoints", type="number", format="float", example=100.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Item created successfully with grades auto-populated"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(GradeableItemCreateRequest $request): JsonResponse
    {
        try {
            $dto = GradeableItemCreateDTO::fromRequest($request->validated());
            $item = $this->service->createItem($dto);
            $responseDTO = new SuccessResponseDTO(201, 'Item created successfully. Grades automatically created for all enrolled students.', $item->toArray());
            return response()->json($responseDTO->toArray(), 201);
        } catch (ValidationException $e) {
            $responseDTO = new ErrorResponseDTO(422, 'Validation error', $e->errors());
            return response()->json($responseDTO->toArray(), 422);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(500, 'Failed to create item', [$e->getMessage()]);
            return response()->json($responseDTO->toArray(), 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/gradeable-items/{id}",
     *     summary="Update a gradeable item",
     *     tags={"Gradeable Items"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Final Exam"),
     *             @OA\Property(property="maxPoints", type="number", format="float", example=150.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Item not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(GradeableItemUpdateRequest $request, int $id): JsonResponse
    {
        try {
            $dto = GradeableItemUpdateDTO::fromRequest($request->validated());
            $item = $this->service->updateItem($id, $dto);
            $responseDTO = new SuccessResponseDTO(200, 'Item updated successfully', $item->toArray());
            return response()->json($responseDTO->toArray(), 200);
        } catch (ModelNotFoundException $e) {
            $responseDTO = new ErrorResponseDTO(404, 'Item not found', [$e->getMessage()]);
            return response()->json($responseDTO->toArray(), 404);
        } catch (ValidationException $e) {
            $responseDTO = new ErrorResponseDTO(422, 'Validation error', $e->errors());
            return response()->json($responseDTO->toArray(), 422);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(500, 'Failed to update item', [$e->getMessage()]);
            return response()->json($responseDTO->toArray(), 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/gradeable-items/{id}",
     *     summary="Delete a gradeable item",
     *     tags={"Gradeable Items"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Item not found"
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->deleteItem($id);
            $responseDTO = new SuccessResponseDTO(200, 'Item deleted successfully. All associated grades have been removed.', null);
            return response()->json($responseDTO->toArray(), 200);
        } catch (ModelNotFoundException $e) {
            $responseDTO = new ErrorResponseDTO(404, 'Item not found', [$e->getMessage()]);
            return response()->json($responseDTO->toArray(), 404);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(500, 'Failed to delete item', [$e->getMessage()]);
            return response()->json($responseDTO->toArray(), 500);
        }
    }
}
