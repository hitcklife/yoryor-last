<?php

namespace App\Exceptions\Api;

class ValidationException extends ApiException
{
    /**
     * Create a new validation exception instance.
     *
     * @param array $errors
     * @param string $message
     * @param int $statusCode
     * @param array $headers
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        array $errors = [],
        string $message = 'The given data was invalid.',
        int $statusCode = 422,
        array $headers = [],
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $errors, $headers, $code, $previous);
    }

    /**
     * Create a new validation exception from a validator.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @param string $message
     * @param int $statusCode
     * @param array $headers
     * @return static
     */
    public static function fromValidator(
        \Illuminate\Contracts\Validation\Validator $validator,
        string $message = 'The given data was invalid.',
        int $statusCode = 422,
        array $headers = []
    ): self {
        return new static(
            $validator->errors()->toArray(),
            $message,
            $statusCode,
            $headers
        );
    }
}
