<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HolidayCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Customize authorization if needed.
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => 'required|date',
            'name' => 'nullable|string|max:255',
        ];
    }
}
