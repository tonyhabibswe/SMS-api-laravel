<?php

namespace App\DTOs;

class ErrorResponseDTO
{
    public int $statusCode;
    public string $message;
    public array $errors;

    public function __construct(int $statusCode, string $message, array $errors)
    {
        $this->statusCode = $statusCode;
        $this->message = $message;
        $this->errors = $errors;
    }

    /**
     * Convert the DTO to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'statusCode' => $this->statusCode,
            'message'    => $this->message,
            'errors'     => $this->errors,
        ];
    }
}
