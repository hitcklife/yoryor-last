<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Auth\Access\AuthorizationException;

class ErrorHandlingService
{
    /**
     * Standard error codes
     */
    public const ERROR_CODES = [
        'VALIDATION_FAILED' => 'validation_failed',
        'NOT_FOUND' => 'not_found',
        'UNAUTHORIZED' => 'unauthorized',
        'FORBIDDEN' => 'forbidden',
        'SERVER_ERROR' => 'server_error',
        'RATE_LIMITED' => 'rate_limited',
        'DUPLICATE_ENTRY' => 'duplicate_entry',
        'INSUFFICIENT_PERMISSIONS' => 'insufficient_permissions',
        'RESOURCE_CONFLICT' => 'resource_conflict',
        'SERVICE_UNAVAILABLE' => 'service_unavailable',
        'INVALID_REQUEST' => 'invalid_request',
        'EXPIRED_TOKEN' => 'expired_token',
        'CONTENT_INAPPROPRIATE' => 'content_inappropriate',
        'OPERATION_FAILED' => 'operation_failed',
        'INVALID_CREDENTIALS' => 'INVALID_CREDENTIALS'
    ];

    /**
     * Handle and format exceptions consistently
     */
    public static function handleException(\Exception $exception, string $context = null): JsonResponse
    {
        $errorData = self::parseException($exception);
        
        // Log the error with context
        self::logError($exception, $context, $errorData);
        
        return response()->json([
            'status' => 'error',
            'message' => $errorData['message'],
            'error_code' => $errorData['code'],
            'details' => $errorData['details']
        ], $errorData['status_code']);
    }

    /**
     * Parse exception into structured error data
     */
    private static function parseException(\Exception $exception): array
    {
        switch (true) {
            case $exception instanceof ValidationException:
                return [
                    'message' => 'Validation failed',
                    'code' => self::ERROR_CODES['VALIDATION_FAILED'],
                    'status_code' => 422,
                    'details' => $exception->errors()
                ];

            case $exception instanceof ModelNotFoundException:
            case $exception instanceof NotFoundHttpException:
                return [
                    'message' => 'Resource not found',
                    'code' => self::ERROR_CODES['NOT_FOUND'],
                    'status_code' => 404,
                    'details' => null
                ];

            case $exception instanceof AuthorizationException:
            case $exception instanceof AccessDeniedHttpException:
                return [
                    'message' => 'Access denied',
                    'code' => self::ERROR_CODES['FORBIDDEN'],
                    'status_code' => 403,
                    'details' => null
                ];

            case $exception instanceof \Illuminate\Auth\AuthenticationException:
                return [
                    'message' => 'Authentication required',
                    'code' => self::ERROR_CODES['UNAUTHORIZED'],
                    'status_code' => 401,
                    'details' => null
                ];

            case $exception instanceof \Illuminate\Database\QueryException:
                // Handle database specific errors
                if (str_contains($exception->getMessage(), 'Duplicate entry')) {
                    return [
                        'message' => 'Duplicate entry - this resource already exists',
                        'code' => self::ERROR_CODES['DUPLICATE_ENTRY'],
                        'status_code' => 409,
                        'details' => null
                    ];
                }
                
                return [
                    'message' => 'Database operation failed',
                    'code' => self::ERROR_CODES['SERVER_ERROR'],
                    'status_code' => 500,
                    'details' => app()->environment('local') ? $exception->getMessage() : null
                ];

            default:
                return [
                    'message' => 'An unexpected error occurred',
                    'code' => self::ERROR_CODES['SERVER_ERROR'],
                    'status_code' => 500,
                    'details' => app()->environment('local') ? $exception->getMessage() : null
                ];
        }
    }

    /**
     * Log error with structured data
     */
    private static function logError(\Exception $exception, string $context = null, array $errorData = null): void
    {
        $logData = [
            'exception_type' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'context' => $context,
            'user_id' => auth()->id(),
            'request_url' => request()->fullUrl(),
            'request_method' => request()->method(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        if ($errorData && $errorData['status_code'] >= 500) {
            Log::error('Application Error', $logData);
        } else {
            Log::warning('Client Error', $logData);
        }
    }

    /**
     * Create standard success response
     */
    public static function successResponse(mixed $data = null, string $message = 'Operation successful', int $statusCode = 200): JsonResponse
    {
        $response = [
            'status' => 'success',
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Create standard error response
     */
    public static function errorResponse(
        string $message, 
        string $errorCode = null, 
        mixed $details = null, 
        int $statusCode = 400
    ): JsonResponse {
        $response = [
            'status' => 'error',
            'message' => $message,
            'error_code' => $errorCode ?? self::ERROR_CODES['INVALID_REQUEST']
        ];

        if ($details !== null) {
            $response['details'] = $details;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Create paginated response
     */
    public static function paginatedResponse($paginator, string $itemKey = 'items', array $meta = []): JsonResponse
    {
        $response = [
            'status' => 'success',
            'data' => [
                $itemKey => $paginator->items(),
                'pagination' => [
                    'total' => $paginator->total(),
                    'per_page' => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'has_more_pages' => $paginator->hasMorePages(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem()
                ]
            ]
        ];

        if (!empty($meta)) {
            $response['data']['meta'] = $meta;
        }

        return response()->json($response);
    }

    /**
     * Validate business logic and return standardized error
     */
    public static function validateBusinessLogic(bool $condition, string $message, string $errorCode = null): ?JsonResponse
    {
        if (!$condition) {
            return self::errorResponse(
                $message, 
                $errorCode ?? self::ERROR_CODES['OPERATION_FAILED'], 
                null, 
                422
            );
        }

        return null;
    }

    /**
     * Handle file upload errors
     */
    public static function handleFileUploadError(\Exception $exception, string $fileType = 'file'): JsonResponse
    {
        $message = match (true) {
            str_contains($exception->getMessage(), 'size') => "The {$fileType} size exceeds the maximum allowed limit",
            str_contains($exception->getMessage(), 'mime') => "The {$fileType} type is not supported",
            str_contains($exception->getMessage(), 'upload') => "File upload failed. Please try again",
            default => "Failed to process {$fileType}"
        };

        return self::errorResponse(
            $message,
            self::ERROR_CODES['OPERATION_FAILED'],
            app()->environment('local') ? $exception->getMessage() : null,
            422
        );
    }

    /**
     * Handle authentication errors
     */
    public static function authenticationError(string $message = 'Authentication required'): JsonResponse
    {
        return self::errorResponse(
            $message,
            self::ERROR_CODES['UNAUTHORIZED'],
            null,
            401
        );
    }

    /**
     * Handle authorization errors
     */
    public static function authorizationError(string $message = 'Access denied'): JsonResponse
    {
        return self::errorResponse(
            $message,
            self::ERROR_CODES['FORBIDDEN'],
            null,
            403
        );
    }

    /**
     * Handle rate limit errors
     */
    public static function rateLimitError(string $message = 'Too many requests', int $retryAfter = null): JsonResponse
    {
        $details = $retryAfter ? ['retry_after' => $retryAfter] : null;
        
        return self::errorResponse(
            $message,
            self::ERROR_CODES['RATE_LIMITED'],
            $details,
            429
        );
    }

    /**
     * Handle validation errors consistently
     */
    public static function validationError(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return self::errorResponse(
            $message,
            self::ERROR_CODES['VALIDATION_FAILED'],
            $errors,
            422
        );
    }

    /**
     * Handle resource not found errors
     */
    public static function notFoundError(string $resource = 'Resource'): JsonResponse
    {
        return self::errorResponse(
            "{$resource} not found",
            self::ERROR_CODES['NOT_FOUND'],
            null,
            404
        );
    }

    /**
     * Handle service unavailable errors
     */
    public static function serviceUnavailableError(string $service = 'Service'): JsonResponse
    {
        return self::errorResponse(
            "{$service} is currently unavailable. Please try again later",
            self::ERROR_CODES['SERVICE_UNAVAILABLE'],
            null,
            503
        );
    }
}