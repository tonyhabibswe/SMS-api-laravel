<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkGradeUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Handle authorization through middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'grades' => [
                'required',
                'array',
                'min:1'
            ],
            'grades.*.enrollmentId' => [
                'required',
                'integer',
                'exists:course_enrollments,id'
            ],
            'grades.*.gradeableItemId' => [
                'required',
                'integer',
                'exists:gradeable_items,id'
            ],
            'grades.*.gradeValue' => [
                'required',
                'numeric',
                'min:0',
                'decimal:0,2'
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'grades.required' => 'Grades array is required.',
            'grades.array' => 'Grades must be an array.',
            'grades.min' => 'At least one grade is required.',
            'grades.*.enrollmentId.required' => 'Enrollment ID is required for each grade.',
            'grades.*.enrollmentId.exists' => 'One or more enrollment IDs do not exist.',
            'grades.*.gradeableItemId.required' => 'Gradeable item ID is required for each grade.',
            'grades.*.gradeableItemId.exists' => 'One or more gradeable item IDs do not exist.',
            'grades.*.gradeValue.required' => 'Grade value is required for each grade.',
            'grades.*.gradeValue.numeric' => 'Grade value must be a number.',
            'grades.*.gradeValue.min' => 'Grade value must be at least 0.',
        ];
    }
}
