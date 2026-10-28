<?php

namespace App\Http\Requests\CourseSection;

use Illuminate\Foundation\Http\FormRequest;

class PreviewGradesTableRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Auth handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * The grades follow the bulk update's rules; GradeService checks that each
     * one is for a student and an item of this course section.
     */
    public function rules(): array
    {
        return [
            'courseSectionId' => 'required|integer|min:1|exists:course_sections,id',
            'grades' => ['sometimes', 'array'],
            'grades.*.enrollmentId' => ['required', 'integer'],
            'grades.*.gradeableItemId' => ['required', 'integer'],
            // null tries the grade as not graded
            'grades.*.gradeValue' => ['present', 'nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'neededItemId' => ['nullable', 'integer'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'grades.array' => 'Grades must be an array.',
            'grades.*.enrollmentId.required' => 'Enrollment ID is required for each grade.',
            'grades.*.gradeableItemId.required' => 'Gradeable item ID is required for each grade.',
            'grades.*.gradeValue.present' => 'Grade value is required for each grade (null means not graded).',
            'grades.*.gradeValue.numeric' => 'Grade value must be a number.',
            'grades.*.gradeValue.min' => 'Grade value must be at least 0.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Get courseSectionId from route parameter
        $this->merge([
            'courseSectionId' => $this->route('courseSectionId'),
        ]);
    }
}
