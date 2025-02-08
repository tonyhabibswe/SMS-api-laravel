<?php

namespace App\Http\Controllers;

use App\DTOs\Semester\SemesterCreateDTO;
use App\DTOs\Semester\SemesterEditDTO;
use App\Http\Requests\SemesterCreateRequest;
use App\Http\Requests\SemesterEditRequest;
use App\Services\CourseSectionService;
use App\Services\SemesterService;
use Illuminate\Http\JsonResponse;

class SemesterController extends Controller
{

    protected SemesterService $semesterService;
    protected CourseSectionService $courseSectionService;


    public function __construct(SemesterService $semesterService, CourseSectionService $courseSectionService)
    {
        $this->semesterService = $semesterService;
        $this->courseSectionService = $courseSectionService;
    }


    /**
     * List all semesters.
     *
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        // Get DTOs from the service layer
        $semesterListDTOs = $this->semesterService->listSemesters();

        // Return the DTOs as JSON with a 200 status code
        return response()->json($semesterListDTOs, 200);
    }

    /**
     * Create a new semester along with optional holidays.
     *
     * @param SemesterCreateRequest $request
     * @return JsonResponse
     */
    public function create(SemesterCreateRequest $request): JsonResponse
    {
        // Build a DTO from the validated request data.
        $createDTO = new SemesterCreateDTO(
            $request->name,
            $request->start_date,
            $request->end_date,
            $request->holidays ?? null
        );

        // Use the service layer to create the semester.
        $semesterListDTO = $this->semesterService->createSemester($createDTO);

        return response()->json($semesterListDTO, 201);
    }

    /**
     * Update a semester.
     *
     * @param SemesterEditRequest $request
     * @return JsonResponse
     */
    public function edit(SemesterEditRequest $request): JsonResponse
    {
        // Build the edit DTO from the validated request data.
        $editDTO = new SemesterEditDTO(
            $request->id,
            $request->name
        );

        // Call the service layer to update the semester.
        $updatedSemesterDTO = $this->semesterService->updateSemester($editDTO);

        // If no semester was found, return a 404 error.
        if (!$updatedSemesterDTO) {
            return response()->json(['message' => 'Semester not found'], 404);
        }

        // Return a success response with the updated semester DTO.
        return response()->json([
            'message'  => 'Semester updated successfully',
            'semester' => $updatedSemesterDTO
        ], 200);
    }

    /**
     * Delete a semester.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        $deleted = $this->semesterService->deleteSemester($id);

        if (!$deleted) {
            return response()->json(['message' => 'Semester not found'], 404);
        }

        return response()->json(['message' => 'Semester deleted successfully'], 200);
    }


    /**
     * List course sections for a given semester ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function listCoursesBySemesterId(int $id): JsonResponse
    {
        $dtoCollection = $this->courseSectionService->listBySemesterId($id);

        return response()->json($dtoCollection, 200);
    }
}