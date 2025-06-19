<?php

namespace App\Exceptions\Api;

class NotFoundException extends ApiException
{
    /**
     * Create a new not found exception instance.
     *
     * @param string $message
     * @param array $errors
     * @param int $statusCode
     * @param array $headers
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        string $message = 'Resource not found.',
        array $errors = [],
        int $statusCode = 404,
        array $headers = [],
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $errors, $headers, $code, $previous);
    }
}
