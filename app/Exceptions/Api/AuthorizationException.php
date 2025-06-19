<?php

namespace App\Exceptions\Api;

class AuthorizationException extends ApiException
{
    /**
     * Create a new authorization exception instance.
     *
     * @param string $message
     * @param array $errors
     * @param int $statusCode
     * @param array $headers
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        string $message = 'This action is unauthorized.',
        array $errors = [],
        int $statusCode = 403,
        array $headers = [],
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $errors, $headers, $code, $previous);
    }
}
