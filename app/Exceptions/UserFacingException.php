<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

/**
 * An error whose message is written for the API client.
 *
 * Throw it for expected failures, such as a record that doesn't exist or a
 * request that can't be carried out. Its message and status code are sent to
 * the client as they are. Any other exception is logged and answered with a
 * generic message, so SQL errors and other internals never reach the client.
 */
class UserFacingException extends RuntimeException
{
    /**
     * @param string $message Message shown to the client.
     * @param int $statusCode HTTP status code of the error response.
     * @param Throwable|null $previous
     */
    public function __construct(string $message, int $statusCode = 400, ?Throwable $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
    }

    /**
     * Get the HTTP status code of the error response.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->getCode();
    }
}
