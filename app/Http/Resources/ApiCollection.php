<?php

namespace App\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ApiCollection extends ResourceCollection
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
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'status' => $this->status,
            'message' => $this->message,
            'data' => $this->collection,
            'meta' => [
                'total' => $this->collection->count(),
                'per_page' => $this->resource->perPage() ?? $this->collection->count(),
                'current_page' => $this->resource->currentPage() ?? 1,
                'last_page' => $this->resource->lastPage() ?? 1,
                'from' => $this->resource->firstItem() ?? 1,
                'to' => $this->resource->lastItem() ?? $this->collection->count(),
            ],
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
