<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GradeableItemCreateRequest extends FormRequest
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
            'categoryId' => [
                'required',
                'integer',
                'exists:gradeable_categories,id'
            ],
            'title' => [
                'required',
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
            'categoryId.required' => 'Category is required.',
            'categoryId.exists' => 'The selected category does not exist.',
            'title.required' => 'Item title is required.',
            'title.max' => 'Item title must not exceed 255 characters.',
            'maxPoints.min' => 'Max points must be at least 0.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default max_points if not provided
        if (!$this->has('maxPoints')) {
            $this->merge(['maxPoints' => 100.00]);
        }
    }
}
