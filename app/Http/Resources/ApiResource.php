<?php

namespace App\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiResource extends JsonResource
{
    /**
     * @var string
     */
    protected $status = 'success';

    /**
     * @var string
     */
    protected $message = '';

    /**
     * @var int
     */
    protected $statusCode = 200;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @param  string  $status
     * @param  string  $message
     * @param  int  $statusCode
     * @return void
     */
    public function __construct($resource, string $status = 'success', string $message = '', int $statusCode = 200)
    {
        parent::__construct($resource);
        $this->status = $status;
        $this->message = $message;
        $this->statusCode = $statusCode;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'status' => $this->status,
            'message' => $this->message,
            'data' => $this->resource,
        ];
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toResponse($request): JsonResponse
    {
        return parent::toResponse($request)
            ->setStatusCode($this->statusCode);
    }

    /**
     * Create a new success resource instance.
     *
     * @param  mixed  $resource
     * @param  string  $message
     * @param  int  $statusCode
     * @return static
     */
    public static function success($resource, string $message = 'Success', int $statusCode = 200): self
    {
        return new static($resource, 'success', $message, $statusCode);
    }

    /**
     * Create a new error resource instance.
     *
     * @param  mixed  $resource
     * @param  string  $message
     * @param  int  $statusCode
     * @return static
     */
    public static function error($resource, string $message = 'Error', int $statusCode = 400): self
    {
        return new static($resource, 'error', $message, $statusCode);
    }
}
