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
            'studentId'  => 'required|string',
            'firstName'  => 'required|string',
            'fatherName' => 'required|string',
            'lastName'   => 'required|string',
            'major'       => 'required|string',
            'email'       => 'required|email',
            'campus'      => 'required|string',
        ];
    }
}
