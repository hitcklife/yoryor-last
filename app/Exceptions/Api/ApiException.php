<?php

namespace App\Exceptions\Api;

use Exception;
use Illuminate\Http\JsonResponse;

class ApiException extends Exception
{
    /**
     * @var string
     */
    protected $status = 'error';

    /**
     * @var int
     */
    protected $statusCode = 500;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * Create a new API exception instance.
     *
     * @param string $message
     * @param int $statusCode
     * @param array $errors
     * @param array $headers
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        string $message = 'An error occurred',
        int $statusCode = 500,
        array $errors = [],
        array $headers = [],
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->statusCode = $statusCode;
        $this->errors = $errors;
        $this->headers = $headers;
    }

    /**
     * Get the status code.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the errors.
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get the headers.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Render the exception as an HTTP response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function render(): JsonResponse
    {
        $response = [
            'status' => $this->status,
            'message' => $this->getMessage(),
        ];

        if (!empty($this->errors)) {
            $response['errors'] = $this->errors;
        }

        return new JsonResponse($response, $this->statusCode, $this->headers);
    }
}
