<?php

namespace App\Http\Controllers;

use App\DTOs\ErrorResponseDTO;
use App\DTOs\Holiday\HolidayCreateDTO;
use App\DTOs\SuccessResponseDTO;
use App\Http\Requests\HolidayCreateRequest;
use App\Http\Requests\HolidayEditRequest;
use App\Services\HolidayService;
use Illuminate\Http\JsonResponse;

class HolidayController extends Controller
{
    protected HolidayService $holidayService;
    
    public function __construct(HolidayService $holidayService)
    {
        $this->holidayService = $holidayService;
    }
    
    /**
     * List all holidays for a given semester ID.
     *
     * @param int $id Semester ID.
     * @return JsonResponse
     */
    public function listHolidays(int $id): JsonResponse
    {
        $holidays = $this->holidayService->listHolidaysBySemesterId($id);
        $responseDTO = new SuccessResponseDTO(200, 'Operation successful', $holidays);
        return response()->json($responseDTO, $responseDTO->statusCode);
    }

    /**
     * Create a holiday for a semester and delete all course sessions that match the holiday date.
     *
     * Route: POST /api/semester/{id}/holidays
     *
     * @param HolidayCreateRequest $request
     * @param int $id Semester id.
     * @return JsonResponse
     */
    public function createHoliday(HolidayCreateRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();
        $dto = new HolidayCreateDTO($data['date'], $data['name'] ?? null);

        try {
            $result = $this->holidayService->createHoliday($id, $dto);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO($e->getCode() ?: 400, $e->getMessage(), []);
            return response()->json($responseDTO, $responseDTO->statusCode);
        }

        $responseDTO = new SuccessResponseDTO(201, 'Holiday created successfully', $result);
        return response()->json($responseDTO, $responseDTO->statusCode);
    }

    /**
     * Update the name of a holiday.
     *
     * Route: PUT /api/holidays/{id}
     *
     * @param HolidayEditRequest $request
     * @param int $id Holiday ID.
     * @return JsonResponse
     */
    public function updateHoliday(HolidayEditRequest $request, int $id): JsonResponse
    {
        try {
            $holiday = $this->holidayService->updateHolidayName($id, $request->name);
        } catch (\Exception $e) {
            $responseDTO = new ErrorResponseDTO($e->getCode() ?: 400, $e->getMessage(), []);
            return response()->json($responseDTO, $responseDTO->statusCode);
        }

        $successResponse = new SuccessResponseDTO(200, 'Holiday updated successfully', $holiday);   
        return response()->json($successResponse, $successResponse->statusCode);
    }
}
