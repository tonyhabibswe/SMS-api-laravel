<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HolidayEditRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Adjust authorization logic as needed.
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
        ];
    }
}
