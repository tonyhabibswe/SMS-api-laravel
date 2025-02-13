<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CourseSectionCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'courseId' => 'required|exists:courses,id',
            'semesterId' => 'required|exists:semesters,id',
            'sectionCode' => 'required|string|max:2',
            'courseDays' => 'required|array',
            'courseDays.*' => 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'startSessionTime' => 'required|date_format:H:i',
            'endSessionTime' => 'required|date_format:H:i|after:start_session_time',
            'room' => 'required|string|max:100',
        ];
    }
}
