<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\ApiCollection;

class UserCollection extends ApiCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = UserResource::class;
}
