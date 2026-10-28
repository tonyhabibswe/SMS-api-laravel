<?php

use App\Exceptions\UserFacingException;
use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

if (!function_exists('getDefaultMessageForStatusCode')) {
    function getDefaultMessageForStatusCode($statusCode)
    {
        return match ($statusCode) {
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            422 => 'Unprocessable Entity',
            500 => 'Internal Server Error',
            default => 'An error occurred'
        };
    }
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(ForceJsonResponse::class);
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Expected failures with a message written for the client; nothing to log.
        $exceptions->dontReport(UserFacingException::class);

        // Only messages written for the client are sent back. Anything else gets a
        // generic message; Laravel reports (logs) it before rendering, so the details
        // stay in the server logs. Controller::exceptionResponse() applies the same
        // rule to exceptions caught in controllers.
        $exceptions->render(function (Throwable $exception, Request $request) {
            // Default status code for unexpected errors
            $statusCode = 500;
            $message = 'An error occurred';
            $errors = [];
            if ($exception instanceof UserFacingException) {
                $statusCode = $exception->getStatusCode();
                $message = $exception->getMessage();
            }
            // If it's an HTTP exception, we use the HTTP status code and message
            if ($exception instanceof AuthenticationException) {
                $statusCode = 401;
                $message = $exception->getMessage() ?: getDefaultMessageForStatusCode($statusCode);
            }
            if ($exception instanceof HttpException) {
                $statusCode = $exception->getStatusCode();
                // Laravel turns a ModelNotFoundException into a 404 whose message names the model class.
                $message = $exception->getPrevious() instanceof ModelNotFoundException
                    ? getDefaultMessageForStatusCode($statusCode)
                    : ($exception->getMessage() ?: getDefaultMessageForStatusCode($statusCode));
            }

            // For validation exceptions, use a 422 status code
            if ($exception instanceof \Illuminate\Validation\ValidationException) {
                $statusCode = 422;
                $message = 'Validation error';
                $errors = $exception->errors();
            }
            return response()->json([
                'statusCode' => $statusCode,
                'message' => $message,
                'errors' => $errors,

            ], $statusCode);
        });
        //
    })->create();
