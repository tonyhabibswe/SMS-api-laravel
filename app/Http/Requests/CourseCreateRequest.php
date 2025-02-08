<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CourseCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Adjust as needed to authorize only instructors, etc.
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:20|unique:courses,code',
            'name' => 'required|string|max:100',
        ];
    }
}
