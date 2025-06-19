<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    protected $fillable = [
        'name',
        'code',
        'flag',
        'phone_code',
        'phone_template'
    ];

    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class);
    }
}
