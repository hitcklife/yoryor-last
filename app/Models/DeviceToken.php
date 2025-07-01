<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'device_name',
        'brand',
        'model_name',
        'os_name',
        'os_version',
        'device_type',
        'is_device',
        'manufacturer'
    ];

    protected $casts = [
        'is_device' => 'boolean',
    ];

    /**
     * Get the user that owns the device token.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
