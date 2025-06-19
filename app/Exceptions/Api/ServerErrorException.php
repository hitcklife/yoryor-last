<?php

namespace App\Exceptions\Api;

class ServerErrorException extends ApiException
{
    /**
     * Create a new server error exception instance.
     *
     * @param string $message
     * @param array $errors
     * @param int $statusCode
     * @param array $headers
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        string $message = 'Server error occurred.',
        array $errors = [],
        int $statusCode = 500,
        array $headers = [],
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $errors, $headers, $code, $previous);
    }
}
