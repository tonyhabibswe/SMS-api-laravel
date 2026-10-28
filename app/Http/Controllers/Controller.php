<?php

namespace App\Http\Controllers;

use App\DTOs\ErrorResponseDTO;
use App\Exceptions\UserFacingException;
use Illuminate\Http\JsonResponse;
use Throwable;

abstract class Controller
{
    /**
     * Build the error response for an exception caught in a controller action.
     *
     * A UserFacingException is answered with its own message and status code.
     * Any other exception is reported (logged with its stack trace) and answered
     * with a 500 and the given generic message, so the client never sees SQL
     * errors or other internals. bootstrap/app.php applies the same rule to
     * exceptions that no controller catches.
     *
     * @param Throwable $e
     * @param string $message Generic message for exceptions that aren't user-facing.
     * @return JsonResponse
     */
    protected function exceptionResponse(Throwable $e, string $message): JsonResponse
    {
        if ($e instanceof UserFacingException) {
            $responseDTO = new ErrorResponseDTO($e->getStatusCode(), $e->getMessage(), []);
        } else {
            report($e);
            $responseDTO = new ErrorResponseDTO(500, $message, []);
        }

        return response()->json($responseDTO, $responseDTO->statusCode);
    }
}
