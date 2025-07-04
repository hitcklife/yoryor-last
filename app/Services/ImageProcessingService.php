<?php

namespace App\Services;

use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Exception;

class ImageProcessingService
{
    // Image dimensions
    const THUMBNAIL_SIZE = 150;
    const MEDIUM_SIZE = 500;
    const ORIGINAL_MAX_SIZE = 1920;
    
    // Image quality settings
    const THUMBNAIL_QUALITY = 85;
    const MEDIUM_QUALITY = 90;
    const ORIGINAL_QUALITY = 95;
    
    /**
     * Process uploaded image and create multiple sizes
     *
     * @param UploadedFile $file
     * @param int $userId
     * @param string $prefix
     * @return array
     * @throws Exception
     */
    public function processImage(UploadedFile $file, int $userId, string $prefix = 'photo'): array
    {
        try {
            // Create directory for user if it doesn't exist
            $userDir = "photos/{$userId}";
            if (!Storage::disk('public')->exists($userDir)) {
                Storage::disk('public')->makeDirectory($userDir);
            }
            
            // Generate unique filename
            $filename = $prefix . '_' . time() . '_' . uniqid();
            $extension = $file->getClientOriginalExtension();
            
            // Process original image
            $originalImage = Image::make($file);
            
            // Fix orientation based on EXIF data
            $originalImage->orientate();
            
            // Get original dimensions for metadata
            $originalWidth = $originalImage->width();
            $originalHeight = $originalImage->height();
            
            // Resize original if too large
            if ($originalWidth > self::ORIGINAL_MAX_SIZE || $originalHeight > self::ORIGINAL_MAX_SIZE) {
                $originalImage->resize(self::ORIGINAL_MAX_SIZE, self::ORIGINAL_MAX_SIZE, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            
            // Create file paths
            $originalPath = "{$userDir}/{$filename}_original.{$extension}";
            $mediumPath = "{$userDir}/{$filename}_medium.{$extension}";
            $thumbnailPath = "{$userDir}/{$filename}_thumbnail.{$extension}";
            
            // Save original
            $originalImage->save(storage_path("app/public/{$originalPath}"), self::ORIGINAL_QUALITY);
            
            // Create and save medium size
            $mediumImage = clone $originalImage;
            $mediumImage->resize(self::MEDIUM_SIZE, self::MEDIUM_SIZE, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $mediumImage->save(storage_path("app/public/{$mediumPath}"), self::MEDIUM_QUALITY);
            
            // Create and save thumbnail
            $thumbnailImage = clone $originalImage;
            $thumbnailImage->fit(self::THUMBNAIL_SIZE, self::THUMBNAIL_SIZE);
            $thumbnailImage->save(storage_path("app/public/{$thumbnailPath}"), self::THUMBNAIL_QUALITY);
            
            // Generate metadata
            $metadata = [
                'original_width' => $originalWidth,
                'original_height' => $originalHeight,
                'processed_width' => $originalImage->width(),
                'processed_height' => $originalImage->height(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'processing_timestamp' => now()->toISOString()
            ];
            
            return [
                'original_url' => Storage::url($originalPath),
                'medium_url' => Storage::url($mediumPath),
                'thumbnail_url' => Storage::url($thumbnailPath),
                'metadata' => $metadata,
                'file_paths' => [
                    'original' => $originalPath,
                    'medium' => $mediumPath,
                    'thumbnail' => $thumbnailPath
                ]
            ];
            
        } catch (Exception $e) {
            throw new Exception("Image processing failed: " . $e->getMessage());
        }
    }
    
    /**
     * Delete image files from storage
     *
     * @param array $filePaths
     * @return bool
     */
    public function deleteImageFiles(array $filePaths): bool
    {
        try {
            foreach ($filePaths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
            return true;
        } catch (Exception $e) {
            \Log::error("Failed to delete image files: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Extract file paths from URLs for deletion
     *
     * @param string $originalUrl
     * @param string $mediumUrl
     * @param string $thumbnailUrl
     * @return array
     */
    public function extractFilePaths(string $originalUrl, string $mediumUrl, string $thumbnailUrl): array
    {
        return [
            'original' => str_replace('/storage/', '', $originalUrl),
            'medium' => str_replace('/storage/', '', $mediumUrl),
            'thumbnail' => str_replace('/storage/', '', $thumbnailUrl)
        ];
    }
    
    /**
     * Get appropriate image URL based on context
     *
     * @param string $originalUrl
     * @param string $mediumUrl
     * @param string $thumbnailUrl
     * @param string $size
     * @return string
     */
    public function getImageUrl(string $originalUrl, string $mediumUrl, string $thumbnailUrl, string $size = 'medium'): string
    {
        switch ($size) {
            case 'thumbnail':
                return $thumbnailUrl;
            case 'original':
                return $originalUrl;
            case 'medium':
            default:
                return $mediumUrl;
        }
    }
    
    /**
     * Validate image file
     *
     * @param UploadedFile $file
     * @return bool
     */
    public function validateImage(UploadedFile $file): bool
    {
        // Check if file is actually an image
        if (!$file->isValid()) {
            return false;
        }
        
        // Check MIME type
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            return false;
        }
        
        // Check file size (10MB max)
        if ($file->getSize() > 10 * 1024 * 1024) {
            return false;
        }
        
        return true;
    }
} 