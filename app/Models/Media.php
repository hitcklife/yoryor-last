<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'media_url',
        'media_type'

    ];

    protected $casts = [
        'uploaded_at' => 'datetime'
    ];


    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
}
