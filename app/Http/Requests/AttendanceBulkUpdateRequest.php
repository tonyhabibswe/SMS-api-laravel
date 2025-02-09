<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceBulkUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Adjust authorization logic as needed.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'attendanceIds' => 'required|array',
            'attendanceIds.*' => 'integer|exists:attendances,id',
            'attendance' => 'required|string',
        ];
    }
}
