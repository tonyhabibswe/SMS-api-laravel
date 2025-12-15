<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CoursePassingGradeCreateRequest extends FormRequest
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
            'majorId' => [
                'required',
                'integer',
                'exists:majors,id'
            ],
            'semesterId' => [
                'required',
                'integer',
                'exists:semesters,id'
            ],
            'courseId' => [
                'required',
                'integer',
                'exists:courses,id'
            ],
            'gradeValue' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
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
            'majorId.required' => 'Major is required.',
            'majorId.exists' => 'The selected major does not exist.',
            'semesterId.required' => 'Semester is required.',
            'semesterId.exists' => 'The selected semester does not exist.',
            'courseId.required' => 'Course is required.',
            'courseId.exists' => 'The selected course does not exist.',
            'gradeValue.required' => 'Grade value is required.',
            'gradeValue.numeric' => 'Grade value must be a number.',
            'gradeValue.min' => 'Grade value must be at least 0.',
            'gradeValue.max' => 'Grade value must not exceed 100.',
            'gradeValue.decimal' => 'Grade value must have at most 2 decimal places.',
        ];
    }

    /**
     * Configure validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Check for unique combination
            $exists = \App\Models\CoursePassingGrade::where('major_id', $this->majorId)
                ->where('semester_id', $this->semesterId)
                ->where('course_id', $this->courseId)
                ->exists();

            if ($exists) {
                $validator->errors()->add(
                    'combination',
                    'A passing grade for this major, semester, and course combination already exists.'
                );
            }
        });
    }
}
