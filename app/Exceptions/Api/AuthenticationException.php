<?php

namespace App\Exceptions\Api;

class AuthenticationException extends ApiException
{
    /**
     * Create a new authentication exception instance.
     *
     * @param string $message
     * @param array $errors
     * @param int $statusCode
     * @param array $headers
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        string $message = 'Unauthenticated.',
        array $errors = [],
        int $statusCode = 401,
        array $headers = [],
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $errors, $headers, $code, $previous);
    }
}
