<?php

namespace App\Http\Requests\CourseSection;

use Illuminate\Foundation\Http\FormRequest;

class GetGradesTableRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'courseSectionId' => 'required|integer|min:1|exists:course_sections,id',
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
