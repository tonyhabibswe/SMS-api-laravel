<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GradeableItemUpdateRequest extends FormRequest
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
            'title' => [
                'sometimes',
                'string',
                'max:255'
            ],
            'maxPoints' => [
                'sometimes',
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
            'title.max' => 'Item title must not exceed 255 characters.',
            'maxPoints.min' => 'Max points must be at least 0.',
        ];
    }
}
