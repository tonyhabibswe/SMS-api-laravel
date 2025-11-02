<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CoursePassingGradeUpdateRequest extends FormRequest
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
            'major_id' => [
                'required',
                'integer',
                'exists:majors,id'
            ],
            'semester_id' => [
                'required',
                'integer',
                'exists:semesters,id'
            ],
            'course_id' => [
                'required',
                'integer',
                'exists:courses,id'
            ],
            'grade_value' => [
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
            'major_id.required' => 'Major is required.',
            'major_id.exists' => 'The selected major does not exist.',
            'semester_id.required' => 'Semester is required.',
            'semester_id.exists' => 'The selected semester does not exist.',
            'course_id.required' => 'Course is required.',
            'course_id.exists' => 'The selected course does not exist.',
            'grade_value.required' => 'Grade value is required.',
            'grade_value.numeric' => 'Grade value must be a number.',
            'grade_value.min' => 'Grade value must be at least 0.',
            'grade_value.max' => 'Grade value must not exceed 100.',
            'grade_value.decimal' => 'Grade value must have at most 2 decimal places.',
        ];
    }

    /**
     * Configure validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $passingGradeId = $this->route('id');

            // Check for unique combination (excluding current record)
            $exists = \App\Models\CoursePassingGrade::where('major_id', $this->major_id)
                ->where('semester_id', $this->semester_id)
                ->where('course_id', $this->course_id)
                ->where('id', '!=', $passingGradeId)
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
