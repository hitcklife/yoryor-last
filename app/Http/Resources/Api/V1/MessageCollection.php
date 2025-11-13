<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\ApiCollection;

class MessageCollection extends ApiCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = MessageResource::class;
}
