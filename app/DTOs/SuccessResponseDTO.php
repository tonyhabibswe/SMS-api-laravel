<?php

namespace App\DTOs;

class SuccessResponseDTO
{
    public int $statusCode;
    public string $message;
    public $data;

    public function __construct(int $statusCode, string $message, $data)
    {
        $this->statusCode = $statusCode;
        $this->message = $message;
        $this->data = $data;
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
            'data'       => $this->data,
        ];
    }
}
