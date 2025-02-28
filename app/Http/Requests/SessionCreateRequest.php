<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SessionCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Adjust as needed (e.g. check if the user is an instructor)
        return true;
    }

    public function rules(): array
    {
        return [
            'sessionStart' => 'required|date',
            'sessionEnd'   => 'required|date|after:sessionStart',
            'room'          => 'required|string',
        ];
    }
}
