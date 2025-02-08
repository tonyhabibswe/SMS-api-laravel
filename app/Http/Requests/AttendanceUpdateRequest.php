<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Adjust this logic based on your authorization requirements.
        return true;
    }

    public function rules(): array
    {
        return [
            'attendance' => 'required|string|max:10', // Adjust the rules if needed
        ];
    }
}
