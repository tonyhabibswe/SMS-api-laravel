<?php

namespace App\Http\Controllers;

use App\DTOs\StudentCourseStatus\StudentCourseStatusDTO;
use App\DTOs\ErrorResponseDTO;
use App\DTOs\SuccessResponseDTO;
use App\Http\Requests\StudentCourseStatusCreateRequest;
use App\Http\Requests\StudentCourseStatusUpdateRequest;
use App\Services\StudentCourseStatusService;
use Illuminate\Http\JsonResponse;

class StudentCourseStatusController extends Controller
{
    protected StudentCourseStatusService $service;

    public function __construct(StudentCourseStatusService $service)
    {
        $this->service = $service;
    }

    /**
     * Get all statuses.
     */
    public function index(): JsonResponse
    {
        $statuses = $this->service->getAllStatuses();

        $dtos = $statuses->map(function ($status) {
            return StudentCourseStatusDTO::fromModel($status);
        });

        $responseDTO = new SuccessResponseDTO(200, 'Statuses retrieved successfully', $dtos);
        return response()->json($responseDTO, 200);
    }

    /**
     * Get status by ID.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $status = $this->service->getStatusById($id);

            if (!$status) {
                $responseDTO = new ErrorResponseDTO(404, 'Status not found', []);
                return response()->json($responseDTO, 404);
            }

            $dto = StudentCourseStatusDTO::fromModel($status);
            $responseDTO = new SuccessResponseDTO(200, 'Status retrieved successfully', $dto);
            return response()->json($responseDTO, 200);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(500, $e->getMessage(), []);
            return response()->json($responseDTO, 500);
        }
    }

    /**
     * Get status by name.
     */
    public function findByName(string $name): JsonResponse
    {
        try {
            $status = $this->service->getStatusByName($name);

            if (!$status) {
                $responseDTO = new ErrorResponseDTO(404, 'Status not found', []);
                return response()->json($responseDTO, 404);
            }

            $dto = StudentCourseStatusDTO::fromModel($status);
            $responseDTO = new SuccessResponseDTO(200, 'Status retrieved successfully', $dto);
            return response()->json($responseDTO, 200);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(500, $e->getMessage(), []);
            return response()->json($responseDTO, 500);
        }
    }

    /**
     * Create a new status.
     */
    public function store(StudentCourseStatusCreateRequest $request): JsonResponse
    {
        try {
            $status = $this->service->createStatus($request->validated());

            $dto = StudentCourseStatusDTO::fromModel($status);
            $responseDTO = new SuccessResponseDTO(201, 'Status created successfully', $dto);
            return response()->json($responseDTO, 201);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(400, $e->getMessage(), []);
            return response()->json($responseDTO, 400);
        }
    }

    /**
     * Update a status.
     */
    public function update(StudentCourseStatusUpdateRequest $request, int $id): JsonResponse
    {
        try {
            $status = $this->service->updateStatus($id, $request->validated());

            $dto = StudentCourseStatusDTO::fromModel($status);
            $responseDTO = new SuccessResponseDTO(200, 'Status updated successfully', $dto);
            return response()->json($responseDTO, 200);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(404, $e->getMessage(), []);
            return response()->json($responseDTO, 404);
        }
    }

    /**
     * Delete a status.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->deleteStatus($id);

            $responseDTO = new SuccessResponseDTO(200, 'Status deleted successfully', []);
            return response()->json($responseDTO, 200);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO(400, $e->getMessage(), []);
            return response()->json($responseDTO, 400);
        }
    }
}
