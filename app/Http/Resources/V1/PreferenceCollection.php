<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\ApiCollection;

class PreferenceCollection extends ApiCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = PreferenceResource::class;
}
