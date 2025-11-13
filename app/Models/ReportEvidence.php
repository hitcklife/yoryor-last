<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ReportEvidence extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'evidence_type',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'file_size' => 'integer',
    ];

    /**
     * Get the report
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(EnhancedUserReport::class, 'report_id');
    }

    /**
     * Get evidence type display name
     */
    public function getEvidenceTypeDisplayNameAttribute(): string
    {
        return match ($this->evidence_type) {
            'screenshot' => 'Screenshot',
            'chat_log' => 'Chat Log',
            'photo' => 'Photo',
            'video' => 'Video',
            'document' => 'Document',
            'audio' => 'Audio Recording',
            default => ucfirst($this->evidence_type)
        };
    }

    /**
     * Get file size in human readable format
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Get file URL
     */
    public function getFileUrlAttribute(): string
    {
        return Storage::disk('private')->url($this->file_path);
    }

    /**
     * Check if file is an image
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if file is a video
     */
    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    /**
     * Check if file is audio
     */
    public function isAudio(): bool
    {
        return str_starts_with($this->mime_type, 'audio/');
    }

    /**
     * Check if file is a document
     */
    public function isDocument(): bool
    {
        return in_array($this->mime_type, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain',
        ]);
    }

    /**
     * Get file extension
     */
    public function getFileExtensionAttribute(): string
    {
        return pathinfo($this->original_filename, PATHINFO_EXTENSION);
    }

    /**
     * Get thumbnail URL (for images and videos)
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->isImage()) {
            return $this->file_url;
        }

        // For videos, you might want to generate thumbnails
        if ($this->isVideo()) {
            // Implement video thumbnail generation logic
            return null;
        }

        return null;
    }

    /**
     * Scope for specific evidence type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('evidence_type', $type);
    }

    /**
     * Scope for images
     */
    public function scopeImages($query)
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    /**
     * Scope for videos
     */
    public function scopeVideos($query)
    {
        return $query->where('mime_type', 'like', 'video/%');
    }

    /**
     * Delete file when model is deleted
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($evidence) {
            if (Storage::disk('private')->exists($evidence->file_path)) {
                Storage::disk('private')->delete($evidence->file_path);
            }
        });
    }
}