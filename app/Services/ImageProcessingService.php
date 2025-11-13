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
    const LARGE_SIZE = 1200;
    
    // Image quality settings
    const THUMBNAIL_QUALITY = 85;
    const MEDIUM_QUALITY = 90;
    const LARGE_QUALITY = 95;
    
    protected $mediaUploadService;
    
    public function __construct(MediaUploadService $mediaUploadService)
    {
        $this->mediaUploadService = $mediaUploadService;
    }
    
    /**
     * Process uploaded image and create multiple sizes (thumbnail, medium, and large)
     * Note: The "original" URL now contains a large size (1200px) instead of the full original
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
            // Use the MediaUploadService for S3 upload
            $result = $this->mediaUploadService->uploadMedia($file, 'profile', $userId, [
                'prefix' => $prefix
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            throw new Exception("Image processing failed: " . $e->getMessage());
        }
    }
    
    /**
     * Delete image files from Cloudflare R2 storage
     *
     * @param array $filePaths
     * @return bool
     */
    public function deleteImageFiles(array $filePaths): bool
    {
        return $this->mediaUploadService->deleteMediaFiles($filePaths);
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
        // Extract R2 paths from URLs
        $originalPath = $this->extractS3PathFromUrl($originalUrl);
        $mediumPath = $this->extractS3PathFromUrl($mediumUrl);
        $thumbnailPath = $this->extractS3PathFromUrl($thumbnailUrl);
        
        return [
            'original' => $originalPath,
            'medium' => $mediumPath,
            'thumbnail' => $thumbnailPath
        ];
    }
    
    /**
     * Extract R2 path from URL
     *
     * @param string $url
     * @return string|null
     */
    private function extractS3PathFromUrl(string $url): ?string
    {
        if (empty($url)) {
            return null;
        }
        
        // Parse the URL to extract the path
        $parsedUrl = parse_url($url);
        if (!$parsedUrl || !isset($parsedUrl['path'])) {
            return null;
        }
        
        // Remove leading slash from path
        $path = ltrim($parsedUrl['path'], '/');
        
        // If using Cloudflare custom domain, the path might be the full R2 key
        // If using direct R2 URLs, we might need to remove the bucket name
        $bucketName = config('filesystems.disks.r2.bucket');
        if ($bucketName && str_starts_with($path, $bucketName . '/')) {
            $path = substr($path, strlen($bucketName) + 1);
        }
        
        return $path ?: null;
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
    public function getImageUrl(string $originalUrl, string $mediumUrl = null, string $thumbnailUrl = null, string $size = 'medium'): string
    {
        return $this->mediaUploadService->getMediaUrl($originalUrl, $mediumUrl, $thumbnailUrl, $size);
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
    
    /**
     * Process image for profile photos with specific requirements
     *
     * @param UploadedFile $file
     * @param int $userId
     * @param bool $isProfilePhoto
     * @return array
     * @throws Exception
     */
    public function processProfileImage(UploadedFile $file, int $userId, bool $isProfilePhoto = false): array
    {
        try {
            $context = $isProfilePhoto ? 'profile_photos' : 'photos';
            $result = $this->mediaUploadService->uploadMedia($file, $context, $userId, [
                'is_profile_photo' => $isProfilePhoto
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            throw new Exception("Profile image processing failed: " . $e->getMessage());
        }
    }
} 