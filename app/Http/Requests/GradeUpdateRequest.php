<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GradeUpdateRequest extends FormRequest
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
            'gradeValue' => [
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
            'gradeValue.required' => 'Grade value is required.',
            'gradeValue.numeric' => 'Grade value must be a number.',
            'gradeValue.min' => 'Grade value must be at least 0.',
        ];
    }
}
