<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CourseEditRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Adjust as needed for authorization
        return true;
    }

    public function rules(): array
    {
        // Retrieve the course id from the route parameters.
        $id = $this->route('id');
        return [
            'code' => "required|string|max:20|unique:courses,code,{$id}",
            'name' => 'required|string|max:100',
        ];
    }
}
