<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return $this->handleApiException($e, $request);
            }
        });
    }

    /**
     * Handle unauthenticated users for web routes
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Redirect to start page for web routes
        return redirect()->route('start');
    }

    /**
     * Handle API exceptions
     *
     * @param \Throwable $exception
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    private function handleApiException(Throwable $exception, Request $request)
    {
        $statusCode = 500;
        $errorCode = 'server_error';
        $message = 'Server Error';

        // Handle specific exception types
        if ($exception instanceof AuthenticationException) {
            $statusCode = 401;
            $errorCode = 'unauthenticated';
            $message = 'Unauthenticated';
        } elseif ($exception instanceof AuthorizationException) {
            $statusCode = 403;
            $errorCode = 'forbidden';
            $message = 'Forbidden';
        } elseif ($exception instanceof ModelNotFoundException) {
            $statusCode = 404;
            $errorCode = 'resource_not_found';
            $message = 'Resource not found';
        } elseif ($exception instanceof NotFoundHttpException) {
            $statusCode = 404;
            $errorCode = 'endpoint_not_found';
            $message = 'Endpoint not found';
        } elseif ($exception instanceof MethodNotAllowedHttpException) {
            $statusCode = 405;
            $errorCode = 'method_not_allowed';
            $message = 'Method not allowed';
        } elseif ($exception instanceof ValidationException) {
            $statusCode = 422;
            $errorCode = 'validation_failed';
            $message = 'Validation failed';

            return response()->json([
                'status' => 'error',
                'message' => $message,
                'error_code' => $errorCode,
                'errors' => $exception->errors()
            ], $statusCode);
        } elseif ($exception instanceof HttpException) {
            $statusCode = $exception->getStatusCode();
            $errorCode = 'http_error';
            $message = $exception->getMessage() ?: 'HTTP Error';
        }

        $response = [
            'status' => 'error',
            'message' => $message,
            'error_code' => $errorCode,
        ];

        // Add exception details in non-production environments
        if (!app()->environment('production')) {
            $response['exception'] = get_class($exception);
            $response['error'] = $exception->getMessage();
            $response['file'] = $exception->getFile();
            $response['line'] = $exception->getLine();
            $response['trace'] = $exception->getTrace();
        }

        return response()->json($response, $statusCode);
    }
}
