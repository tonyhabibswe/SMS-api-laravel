<?php

namespace App\Http\Controllers;

use App\DTOs\GradeableCategory\GradeableCategoryCreateDTO;
use App\DTOs\GradeableCategory\GradeableCategoryUpdateDTO;
use App\DTOs\ErrorResponseDTO;
use App\DTOs\SuccessResponseDTO;
use App\Http\Requests\GradeableCategoryCreateRequest;
use App\Http\Requests\GradeableCategoryUpdateRequest;
use App\Services\GradeableCategoryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Gradeable Categories",
 *     description="API endpoints for managing gradeable categories"
 * )
 */
class GradeableCategoryController extends Controller
{
    protected GradeableCategoryService $service;

    public function __construct(GradeableCategoryService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/api/gradeable-categories",
     *     summary="Get all gradeable categories",
     *     tags={"Gradeable Categories"},
     *     @OA\Response(
     *         response=200,
     *         description="Gradeable categories retrieved successfully"
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $categories = $this->service->getAllCategories();
            $responseDTO = new SuccessResponseDTO(200, 'Gradeable categories retrieved successfully', $categories->map->toArray()->toArray());
            return response()->json($responseDTO->toArray(), 200);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(500, 'Failed to retrieve gradeable categories', [$e->getMessage()]);
            return response()->json($responseDTO->toArray(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/gradeable-categories/course-section/{courseSectionId}",
     *     summary="Get all categories for a course section",
     *     tags={"Gradeable Categories"},
     *     @OA\Parameter(
     *         name="courseSectionId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categories retrieved successfully"
     *     )
     * )
     */
    public function getByCourseSection(int $courseSectionId): JsonResponse
    {
        try {
            $categories = $this->service->getCategoriesByCourseSection($courseSectionId);
            $responseDTO = new SuccessResponseDTO(200, 'Categories retrieved successfully', $categories->map->toArray()->toArray());
            return response()->json($responseDTO->toArray(), 200);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(500, 'Failed to retrieve categories', [$e->getMessage()]);
            return response()->json($responseDTO->toArray(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/gradeable-categories/{id}",
     *     summary="Get a specific gradeable category",
     *     tags={"Gradeable Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category retrieved successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $category = $this->service->getCategoryById($id);
            $responseDTO = new SuccessResponseDTO(200, 'Category retrieved successfully', $category->toArray());
            return response()->json($responseDTO->toArray(), 200);
        } catch (ModelNotFoundException $e) {
            $responseDTO = new ErrorResponseDTO(404, 'Category not found', [$e->getMessage()]);
            return response()->json($responseDTO->toArray(), 404);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(500, 'Failed to retrieve category', [$e->getMessage()]);
            return response()->json($responseDTO->toArray(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/gradeable-categories",
     *     summary="Create a new gradeable category",
     *     tags={"Gradeable Categories"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="courseSectionId", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Exams"),
     *             @OA\Property(property="weightPercent", type="number", format="float", example=50.00),
     *             @OA\Property(property="algorithm", type="string", example="AVERAGE")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category created successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(GradeableCategoryCreateRequest $request): JsonResponse
    {
        try {
            $dto = GradeableCategoryCreateDTO::fromRequest($request->validated());
            $category = $this->service->createCategory($dto);
            $responseDTO = new SuccessResponseDTO(201, 'Category created successfully', $category->toArray());
            return response()->json($responseDTO->toArray(), 201);
        } catch (ValidationException $e) {
            $responseDTO = new ErrorResponseDTO(422, 'Validation error', $e->errors());
            return response()->json($responseDTO->toArray(), 422);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(500, 'Failed to create category', [$e->getMessage()]);
            return response()->json($responseDTO->toArray(), 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/gradeable-categories/{id}",
     *     summary="Update a gradeable category",
     *     tags={"Gradeable Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Midterms"),
     *             @OA\Property(property="weightPercent", type="number", format="float", example=40.00),
     *             @OA\Property(property="algorithm", type="string", example="PICK_HIGHEST")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(GradeableCategoryUpdateRequest $request, int $id): JsonResponse
    {
        try {
            $dto = GradeableCategoryUpdateDTO::fromRequest($request->validated());
            $category = $this->service->updateCategory($id, $dto);
            $responseDTO = new SuccessResponseDTO(200, 'Category updated successfully', $category->toArray());
            return response()->json($responseDTO->toArray(), 200);
        } catch (ModelNotFoundException $e) {
            $responseDTO = new ErrorResponseDTO(404, 'Category not found', [$e->getMessage()]);
            return response()->json($responseDTO->toArray(), 404);
        } catch (ValidationException $e) {
            $responseDTO = new ErrorResponseDTO(422, 'Validation error', $e->errors());
            return response()->json($responseDTO->toArray(), 422);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(500, 'Failed to update category', [$e->getMessage()]);
            return response()->json($responseDTO->toArray(), 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/gradeable-categories/{id}",
     *     summary="Delete a gradeable category",
     *     tags={"Gradeable Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->deleteCategory($id);
            $responseDTO = new SuccessResponseDTO(200, 'Category deleted successfully. All associated items and grades have been removed.', null);
            return response()->json($responseDTO->toArray(), 200);
        } catch (ModelNotFoundException $e) {
            $responseDTO = new ErrorResponseDTO(404, 'Category not found', [$e->getMessage()]);
            return response()->json($responseDTO->toArray(), 404);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(500, 'Failed to delete category', [$e->getMessage()]);
            return response()->json($responseDTO->toArray(), 500);
        }
    }
}
