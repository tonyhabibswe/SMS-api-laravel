<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Adjust authorization as needed.
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id'  => 'required|string',
            'first_name'  => 'required|string',
            'father_name' => 'required|string',
            'last_name'   => 'required|string',
            'major'       => 'required|string',
            'email'       => 'required|email',
            'campus'      => 'required|string',
        ];
    }
}
