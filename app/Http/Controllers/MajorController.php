<?php

namespace App\Http\Controllers;

use App\DTOs\ErrorResponseDTO;
use App\DTOs\SuccessResponseDTO;
use App\Http\Requests\MajorUpdateLabelRequest;
use App\Services\MajorService;
use Illuminate\Http\JsonResponse;

class MajorController extends Controller
{
    protected MajorService $majorService;

    public function __construct(MajorService $majorService)
    {
        $this->majorService = $majorService;
    }

    /**
     * List all majors.
     *
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $majorDTOs = $this->majorService->listMajors();
        $responseDTO = new SuccessResponseDTO(200, 'Operation successful', $majorDTOs);

        return response()->json($responseDTO, 200);
    }

    /**
     * List all majors with student count.
     *
     * @return JsonResponse
     */
    public function listWithStudentCount(): JsonResponse
    {
        $majorDTOs = $this->majorService->listMajorsWithStudentCount();
        $responseDTO = new SuccessResponseDTO(200, 'Operation successful', $majorDTOs);

        return response()->json($responseDTO, 200);
    }

    /**
     * Update major label.
     *
     * @param MajorUpdateLabelRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateLabel(MajorUpdateLabelRequest $request, int $id): JsonResponse
    {
        $validatedData = $request->validated();

        $majorDTO = $this->majorService->updateMajorLabel($id, $validatedData['label']);

        if (!$majorDTO) {
            $responseDTO = new ErrorResponseDTO(404, "Major not found", []);
            return response()->json($responseDTO, 404);
        }

        $responseDTO = new SuccessResponseDTO(200, 'Major label updated successfully', $majorDTO);
        return response()->json($responseDTO, 200);
    }

    /**
     * Find major by system name.
     *
     * @param string $systemName
     * @return JsonResponse
     */
    public function findBySystemName(string $systemName): JsonResponse
    {
        $majorDTO = $this->majorService->findMajorBySystemName($systemName);

        if (!$majorDTO) {
            $responseDTO = new ErrorResponseDTO(404, "Major not found", []);
            return response()->json($responseDTO, 404);
        }

        $responseDTO = new SuccessResponseDTO(200, 'Operation successful', $majorDTO);
        return response()->json($responseDTO, 200);
    }
}
