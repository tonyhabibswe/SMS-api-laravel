<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GradeableCategoryUpdateRequest extends FormRequest
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
            'name' => [
                'sometimes',
                'string',
                'max:255'
            ],
            'weightPercent' => [
                'sometimes',
                'numeric',
                'min:0',
                'max:100',
                'decimal:0,2'
            ],
            'algorithm' => [
                'sometimes',
                Rule::in(['AVERAGE', 'PICK_HIGHEST'])
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.max' => 'Category name must not exceed 255 characters.',
            'weightPercent.min' => 'Weight percentage must be at least 0.',
            'weightPercent.max' => 'Weight percentage must not exceed 100.',
            'algorithm.in' => 'Algorithm must be either AVERAGE or PICK_HIGHEST.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isEmpty()) {
                // Additional validation will be handled in the service layer
                // to check if updated weight sum would exceed 100%
            }
        });
    }
}
