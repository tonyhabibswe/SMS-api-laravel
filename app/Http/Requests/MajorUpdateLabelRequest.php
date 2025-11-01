<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MajorUpdateLabelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'label.required' => 'The label field is required.',
            'label.string' => 'The label must be a string.',
            'label.max' => 'The label may not be greater than 255 characters.',
        ];
    }
}
