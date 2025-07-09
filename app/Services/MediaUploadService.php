<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;
use Intervention\Image\Facades\Image;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Audio\Vorbis;
use FFMpeg\Format\Video\X264;

class MediaUploadService
{
    // Media type constants
    const TYPE_IMAGE = 'image';
    const TYPE_VIDEO = 'video';
    const TYPE_AUDIO = 'audio';
    const TYPE_VOICE = 'voice';
    const TYPE_FILE = 'file';
    
    // Image processing constants - updated sizes
    const THUMBNAIL_SIZE = 50;
    const MEDIUM_SIZE = 300;
    const LARGE_SIZE = 1200;
    
    // Quality settings
    const THUMBNAIL_QUALITY = 85;
    const MEDIUM_QUALITY = 90;
    const LARGE_QUALITY = 95;
    
    // File size limits (in bytes)
    const MAX_IMAGE_SIZE = 10 * 1024 * 1024; // 10MB
    const MAX_VIDEO_SIZE = 100 * 1024 * 1024; // 100MB
    const MAX_AUDIO_SIZE = 50 * 1024 * 1024; // 50MB
    const MAX_FILE_SIZE = 100 * 1024 * 1024; // 100MB
    
    // Allowed MIME types
    const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    const ALLOWED_VIDEO_TYPES = ['video/mp4', 'video/avi', 'video/mov', 'video/webm', 'video/quicktime'];
    const ALLOWED_AUDIO_TYPES = ['audio/mp3', 'audio/wav', 'audio/aac', 'audio/m4a', 'audio/ogg', 'audio/x-m4a'];
    
    protected $ffmpeg;
    
    public function __construct()
    {
        // Initialize FFMpeg
        try {
            // Try to find FFmpeg binaries automatically
            $ffmpegPath = env('FFMPEG_BINARY');
            $ffprobePath = env('FFPROBE_BINARY');
            
            if (!$ffmpegPath) {
                // Try common paths
                $possiblePaths = [
                    '/opt/homebrew/bin/ffmpeg',  // macOS with Homebrew
                    '/usr/bin/ffmpeg',           // Linux
                    '/usr/local/bin/ffmpeg',     // macOS with Homebrew (older)
                    'ffmpeg'                     // System PATH
                ];
                
                foreach ($possiblePaths as $path) {
                    if (file_exists($path) || shell_exec("which $path 2>/dev/null")) {
                        $ffmpegPath = $path;
                        break;
                    }
                }
            }
            
            if (!$ffprobePath) {
                // Try common paths for ffprobe
                $possiblePaths = [
                    '/opt/homebrew/bin/ffprobe',  // macOS with Homebrew
                    '/usr/bin/ffprobe',           // Linux
                    '/usr/local/bin/ffprobe',     // macOS with Homebrew (older)
                    'ffprobe'                     // System PATH
                ];
                
                foreach ($possiblePaths as $path) {
                    if (file_exists($path) || shell_exec("which $path 2>/dev/null")) {
                        $ffprobePath = $path;
                        break;
                    }
                }
            }
            
            if (!$ffmpegPath || !$ffprobePath) {
                throw new Exception('FFmpeg or FFprobe not found. Please install FFmpeg.');
            }
            
            Log::info('FFmpeg configuration', [
                'ffmpeg_path' => $ffmpegPath,
                'ffprobe_path' => $ffprobePath
            ]);
            
            $this->ffmpeg = FFMpeg::create([
                'ffmpeg.binaries'  => $ffmpegPath,
                'ffprobe.binaries' => $ffprobePath,
                'timeout'          => 3600, // The timeout for the underlying process
                'ffmpeg.threads'   => 12,   // The number of threads that FFMpeg should use
            ]);
        } catch (Exception $e) {
            Log::warning('FFMpeg initialization failed: ' . $e->getMessage());
            $this->ffmpeg = null;
        }
    }
    
    /**
     * Upload and process media file
     *
     * @param UploadedFile $file
     * @param string $context (profile, chat, story, etc.)
     * @param int $userId
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function uploadMedia(UploadedFile $file, string $context, int $userId, array $options = []): array
    {
        try {
            // Validate file
            $this->validateFile($file);
            
            // Determine media type
            $mediaType = $this->determineMediaType($file);
            
            // Process based on media type
            switch ($mediaType) {
                case self::TYPE_IMAGE:
                    return $this->processImage($file, $context, $userId, $options);
                case self::TYPE_VIDEO:
                    return $this->processVideo($file, $context, $userId, $options);
                case self::TYPE_AUDIO:
                case self::TYPE_VOICE:
                    return $this->processAudio($file, $context, $userId, $options);
                default:
                    return $this->processFile($file, $context, $userId, $options);
            }
            
        } catch (Exception $e) {
            Log::error('Media upload failed', [
                'user_id' => $userId,
                'context' => $context,
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName()
            ]);
            throw $e;
        }
    }
    
    /**
     * Process image upload with WebP conversion
     *
     * @param UploadedFile $file
     * @param string $context
     * @param int $userId
     * @param array $options
     * @return array
     */
    private function processImage(UploadedFile $file, string $context, int $userId, array $options = []): array
    {
        // Generate unique filename with webp extension
        $filename = $this->generateFilename($file, $context, $userId, 'webp');
        $s3Path = $this->buildS3Path($context, $userId, $filename);
        
        // Create image instance
        $image = Image::make($file);
        
        // Fix orientation based on EXIF data
        $image->orientate();
        
        // Get original dimensions
        $originalWidth = $image->width();
        $originalHeight = $image->height();
        
        // Resize to large size if too large (1200px max)
        if ($originalWidth > self::LARGE_SIZE || $originalHeight > self::LARGE_SIZE) {
            $image->resize(self::LARGE_SIZE, self::LARGE_SIZE, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }
        
        // Convert to WebP and upload large size to S3 (stored as original_url for DB compatibility)
        $originalWebP = $image->encode('webp', self::LARGE_QUALITY);
        $originalUrl = $this->uploadToS3($originalWebP, $s3Path);
        
        // Generate and upload thumbnail (50x50)
        $thumbnailImage = clone $image;
        $thumbnailImage->fit(self::THUMBNAIL_SIZE, self::THUMBNAIL_SIZE);
        $thumbnailPath = $this->getThumbnailPath($s3Path);
        $thumbnailWebP = $thumbnailImage->encode('webp', self::THUMBNAIL_QUALITY);
        $thumbnailUrl = $this->uploadToS3($thumbnailWebP, $thumbnailPath);
        
        // Generate and upload medium size (300x300)
        $mediumImage = clone $image;
        $mediumImage->resize(self::MEDIUM_SIZE, self::MEDIUM_SIZE, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $mediumPath = $this->getMediumPath($s3Path);
        $mediumWebP = $mediumImage->encode('webp', self::MEDIUM_QUALITY);
        $mediumUrl = $this->uploadToS3($mediumWebP, $mediumPath);
        
        // Generate metadata
        $metadata = [
            'original_width' => $originalWidth,
            'original_height' => $originalHeight,
            'processed_width' => $image->width(),
            'processed_height' => $image->height(),
            'file_size' => $file->getSize(),
            'original_mime_type' => $file->getMimeType(),
            'converted_format' => 'webp',
            'processing_timestamp' => now()->toISOString()
        ];
        
        return [
            'original_url' => $originalUrl,
            'medium_url' => $mediumUrl,
            'thumbnail_url' => $thumbnailUrl,
            'media_type' => self::TYPE_IMAGE,
            'metadata' => $metadata,
            'file_paths' => [
                'original' => $s3Path,
                'medium' => $mediumPath,
                'thumbnail' => $thumbnailPath
            ]
        ];
    }
    
    /**
     * Process video upload with MP4 conversion
     *
     * @param UploadedFile $file
     * @param string $context
     * @param int $userId
     * @param array $options
     * @return array
     */
    private function processVideo(UploadedFile $file, string $context, int $userId, array $options = []): array
    {
        if (!$this->ffmpeg) {
            throw new Exception('FFMpeg is not available for video processing');
        }
        
        // Create temporary file for processing
        $tempInputPath = $this->createTempFile($file, 'video_input_');
        $tempOutputPath = tempnam(sys_get_temp_dir(), 'video_output_') . '.mp4';
        
        try {
            // Generate unique filename with mp4 extension
            $filename = $this->generateFilename($file, $context, $userId, 'mp4');
            $s3Path = $this->buildS3Path($context, $userId, $filename);
            
            // Open video with FFMpeg
            $video = $this->ffmpeg->open($tempInputPath);
            
            // Create MP4 format
            $format = new X264('libmp3lame', 'libx264');
            $format->setKiloBitrate(1000)
                   ->setAudioChannels(2)
                   ->setAudioKiloBitrate(128);
            
            // Convert to MP4
            $video->save($format, $tempOutputPath);
            
            // Upload converted video to S3
            $videoContent = file_get_contents($tempOutputPath);
            $videoUrl = $this->uploadToS3($videoContent, $s3Path);
            
            // Generate thumbnail if possible
            $thumbnailUrl = $this->generateVideoThumbnail($tempInputPath, $context, $userId);
            
            // Get video info
            $probe = $this->ffmpeg->getFFProbe();
            $stream = $probe->streams($tempInputPath)->videos()->first();
            
            return [
                'original_url' => $videoUrl,
                'thumbnail_url' => $thumbnailUrl,
                'media_type' => self::TYPE_VIDEO,
                'metadata' => [
                    'file_size' => $file->getSize(),
                    'original_mime_type' => $file->getMimeType(),
                    'converted_format' => 'mp4',
                    'duration' => $probe->format($tempInputPath)->get('duration'),
                    'width' => $stream ? $stream->get('width') : null,
                    'height' => $stream ? $stream->get('height') : null,
                    'original_name' => $file->getClientOriginalName(),
                    'upload_timestamp' => now()->toISOString()
                ],
                'file_paths' => [
                    'original' => $s3Path,
                    'thumbnail' => $thumbnailUrl ? $this->getThumbnailPath($s3Path, 'webp') : null
                ]
            ];
            
        } finally {
            // Clean up temporary files
            if (file_exists($tempInputPath)) {
                unlink($tempInputPath);
            }
            if (file_exists($tempOutputPath)) {
                unlink($tempOutputPath);
            }
        }
    }
    
    /**
     * Process audio upload with OGG conversion for voice messages
     *
     * @param UploadedFile $file
     * @param string $context
     * @param int $userId
     * @param array $options
     * @return array
     */
    private function processAudio(UploadedFile $file, string $context, int $userId, array $options = []): array
    {
        // Determine if it's a voice message
        $isVoiceMessage = !empty($options['is_voice_message']) || 
                         (!empty($options['duration']) && $options['duration'] <= 300);
        
        $mediaType = $isVoiceMessage ? self::TYPE_VOICE : self::TYPE_AUDIO;
        
        // For voice messages or m4a files, convert to OGG
        $shouldConvert = $isVoiceMessage || 
                        in_array($file->getMimeType(), ['audio/m4a', 'audio/x-m4a', 'audio/mp4']);
        
        // Log the processing decision
        Log::info('Audio processing decision', [
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'is_voice_message' => $isVoiceMessage,
            'should_convert' => $shouldConvert,
            'ffmpeg_available' => $this->ffmpeg !== null,
            'media_type' => $mediaType,
            'options' => $options
        ]);
        
        if ($shouldConvert && $this->ffmpeg) {
            Log::info('Converting audio to OGG', [
                'file_name' => $file->getClientOriginalName(),
                'user_id' => $userId,
                'context' => $context
            ]);
            return $this->convertAudioToOgg($file, $context, $userId, $options, $mediaType);
        } else {
            // For regular audio files, upload as-is
            Log::info('Uploading audio directly', [
                'file_name' => $file->getClientOriginalName(),
                'user_id' => $userId,
                'context' => $context,
                'reason' => $shouldConvert ? 'FFmpeg not available' : 'Not a voice message'
            ]);
            return $this->uploadAudioDirect($file, $context, $userId, $options, $mediaType);
        }
    }
    
    /**
     * Convert audio to OGG format
     *
     * @param UploadedFile $file
     * @param string $context
     * @param int $userId
     * @param array $options
     * @param string $mediaType
     * @return array
     */
    private function convertAudioToOgg(UploadedFile $file, string $context, int $userId, array $options, string $mediaType): array
    {
        // Create temporary file for processing
        $tempInputPath = $this->createTempFile($file, 'audio_input_');
        $tempOutputPath = tempnam(sys_get_temp_dir(), 'audio_output_') . '.ogg';
        
        Log::info('Starting audio conversion to OGG', [
            'input_path' => $tempInputPath,
            'output_path' => $tempOutputPath,
            'file_name' => $file->getClientOriginalName(),
            'user_id' => $userId
        ]);
        
        try {
            // Generate unique filename with ogg extension
            $filename = $this->generateFilename($file, $context, $userId, 'ogg');
            $s3Path = $this->buildS3Path($context, $userId, $filename);
            
            Log::info('Generated filename and S3 path', [
                'filename' => $filename,
                's3_path' => $s3Path
            ]);
            
            // Open audio with FFMpeg
            $audio = $this->ffmpeg->open($tempInputPath);
            
            // Create OGG Vorbis format
            $format = new Vorbis();
            $format->setAudioKiloBitrate(96); // Good quality for voice
            
            // Convert to OGG
            $audio->save($format, $tempOutputPath);
            
            Log::info('Audio conversion completed', [
                'output_file_size' => file_exists($tempOutputPath) ? filesize($tempOutputPath) : 0
            ]);
            
            // Upload converted audio to S3
            $audioContent = file_get_contents($tempOutputPath);
            $audioUrl = $this->uploadToS3($audioContent, $s3Path);
            
            // Get audio duration
            $probe = $this->ffmpeg->getFFProbe();
            $duration = $probe->format($tempInputPath)->get('duration');
            
            Log::info('Audio upload completed', [
                'audio_url' => $audioUrl,
                'duration' => $duration,
                's3_path' => $s3Path
            ]);
            
            return [
                'original_url' => $audioUrl,
                'media_type' => $mediaType,
                'metadata' => [
                    'file_size' => $file->getSize(),
                    'original_mime_type' => $file->getMimeType(),
                    'converted_format' => 'ogg',
                    'original_name' => $file->getClientOriginalName(),
                    'duration' => $duration,
                    'is_voice_message' => $mediaType === self::TYPE_VOICE,
                    'upload_timestamp' => now()->toISOString()
                ],
                'file_paths' => [
                    'original' => $s3Path
                ]
            ];
            
        } catch (Exception $e) {
            Log::error('Audio conversion failed', [
                'error' => $e->getMessage(),
                'file_name' => $file->getClientOriginalName(),
                'user_id' => $userId
            ]);
            throw $e;
        } finally {
            // Clean up temporary files
            if (file_exists($tempInputPath)) {
                unlink($tempInputPath);
            }
            if (file_exists($tempOutputPath)) {
                unlink($tempOutputPath);
            }
        }
    }
    
    /**
     * Upload audio file directly without conversion
     *
     * @param UploadedFile $file
     * @param string $context
     * @param int $userId
     * @param array $options
     * @param string $mediaType
     * @return array
     */
    private function uploadAudioDirect(UploadedFile $file, string $context, int $userId, array $options, string $mediaType): array
    {
        $filename = $this->generateFilename($file, $context, $userId);
        $s3Path = $this->buildS3Path($context, $userId, $filename);
        
        // Upload file to S3
        $audioUrl = $this->uploadToS3($file, $s3Path);
        
        // Try to get duration if FFMpeg is available
        $duration = null;
        if ($this->ffmpeg) {
            try {
                $tempPath = $this->createTempFile($file, 'audio_probe_');
                $probe = $this->ffmpeg->getFFProbe();
                $duration = $probe->format($tempPath)->get('duration');
                unlink($tempPath);
            } catch (Exception $e) {
                Log::warning('Could not extract audio duration: ' . $e->getMessage());
            }
        }
        
        return [
            'original_url' => $audioUrl,
            'media_type' => $mediaType,
            'metadata' => [
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'original_name' => $file->getClientOriginalName(),
                'duration' => $duration ?? $options['duration'] ?? null,
                'is_voice_message' => $mediaType === self::TYPE_VOICE,
                'upload_timestamp' => now()->toISOString()
            ],
            'file_paths' => [
                'original' => $s3Path
            ]
        ];
    }
    
    /**
     * Generate video thumbnail
     *
     * @param string $videoPath
     * @param string $context
     * @param int $userId
     * @return string|null
     */
    private function generateVideoThumbnail(string $videoPath, string $context, int $userId): ?string
    {
        if (!$this->ffmpeg) {
            return null;
        }
        
        try {
            $video = $this->ffmpeg->open($videoPath);
            $tempThumbPath = tempnam(sys_get_temp_dir(), 'thumb_') . '.jpg';
            
            // Extract frame at 1 second
            $video->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds(1))
                  ->save($tempThumbPath);
            
            // Convert thumbnail to WebP and resize
            $image = Image::make($tempThumbPath);
            $image->fit(self::THUMBNAIL_SIZE, self::THUMBNAIL_SIZE);
            $webpContent = $image->encode('webp', self::THUMBNAIL_QUALITY);
            
            // Generate S3 path for thumbnail
            $thumbFilename = 'thumb_' . time() . '_' . uniqid() . '.webp';
            $thumbS3Path = $this->buildS3Path($context, $userId, $thumbFilename);
            
            // Upload to S3
            $thumbnailUrl = $this->uploadToS3($webpContent, $thumbS3Path);
            
            // Clean up temp file
            if (file_exists($tempThumbPath)) {
                unlink($tempThumbPath);
            }
            
            return $thumbnailUrl;
            
        } catch (Exception $e) {
            Log::warning('Video thumbnail generation failed: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create temporary file from uploaded file
     *
     * @param UploadedFile $file
     * @param string $prefix
     * @return string
     */
    private function createTempFile(UploadedFile $file, string $prefix): string
    {
        $tempPath = tempnam(sys_get_temp_dir(), $prefix) . '.' . $file->getClientOriginalExtension();
        $file->move(dirname($tempPath), basename($tempPath));
        return $tempPath;
    }
    
    /**
     * Process general file upload
     *
     * @param UploadedFile $file
     * @param string $context
     * @param int $userId
     * @param array $options
     * @return array
     */
    private function processFile(UploadedFile $file, string $context, int $userId, array $options = []): array
    {
        $filename = $this->generateFilename($file, $context, $userId);
        $s3Path = $this->buildS3Path($context, $userId, $filename);
        
        // Upload file to S3
        $fileUrl = $this->uploadToS3($file, $s3Path);
        
        return [
            'original_url' => $fileUrl,
            'media_type' => self::TYPE_FILE,
            'metadata' => [
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'original_name' => $file->getClientOriginalName(),
                'upload_timestamp' => now()->toISOString()
            ],
            'file_paths' => [
                'original' => $s3Path
            ]
        ];
    }
    
    /**
     * Upload file to S3
     *
     * @param mixed $content
     * @param string $path
     * @return string
     */
    private function uploadToS3($content, string $path): string
    {
        try {
            $uploaded = Storage::disk('s3')->put($path, $content);
            
            if (!$uploaded) {
                throw new Exception('Failed to upload file to S3');
            }
            
            return Storage::disk('s3')->url($path);
            
        } catch (Exception $e) {
            Log::error('S3 upload failed', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            throw new Exception('Failed to upload file to S3: ' . $e->getMessage());
        }
    }
    
    /**
     * Validate uploaded file
     *
     * @param UploadedFile $file
     * @throws Exception
     */
    private function validateFile(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw new Exception('Invalid file upload');
        }
        
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();
        
        // Check file size based on type
        $mediaType = $this->determineMediaType($file);
        $maxSize = $this->getMaxFileSize($mediaType);
        
        if ($fileSize > $maxSize) {
            throw new Exception("File size exceeds maximum allowed size of " . $this->formatBytes($maxSize));
        }
        
        // Check MIME type
        $allowedTypes = $this->getAllowedMimeTypes($mediaType);
        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception("File type not allowed. Allowed types: " . implode(', ', $allowedTypes));
        }
    }
    
    /**
     * Determine media type from file
     *
     * @param UploadedFile $file
     * @return string
     */
    private function determineMediaType(UploadedFile $file): string
    {
        $mimeType = $file->getMimeType();
        
        if (in_array($mimeType, self::ALLOWED_IMAGE_TYPES)) {
            return self::TYPE_IMAGE;
        } elseif (in_array($mimeType, self::ALLOWED_VIDEO_TYPES)) {
            return self::TYPE_VIDEO;
        } elseif (in_array($mimeType, self::ALLOWED_AUDIO_TYPES)) {
            return self::TYPE_AUDIO;
        }
        
        return self::TYPE_FILE;
    }
    
    /**
     * Generate unique filename
     *
     * @param UploadedFile $file
     * @param string $context
     * @param int $userId
     * @param string|null $forceExtension
     * @return string
     */
    private function generateFilename(UploadedFile $file, string $context, int $userId, string $forceExtension = null): string
    {
        $extension = $forceExtension ?: $file->getClientOriginalExtension();
        $timestamp = time();
        $uniqueId = uniqid();
        
        // For voice messages, use a shorter, more readable format
        if ($context === 'chat' && ($forceExtension === 'ogg' || $file->getMimeType() === 'audio/m4a')) {
            return "voice_{$userId}_{$timestamp}.{$extension}";
        }
        
        return "{$context}_{$userId}_{$timestamp}_{$uniqueId}.{$extension}";
    }
    
    /**
     * Build S3 path
     *
     * @param string $context
     * @param int $userId
     * @param string $filename
     * @return string
     */
    private function buildS3Path(string $context, int $userId, string $filename): string
    {
        // For voice messages, use a simpler path structure
        if ($context === 'chat' && strpos($filename, 'voice_') === 0) {
            return "media/voice/{$userId}/{$filename}";
        }
        
        return "media/{$context}/{$userId}/{$filename}";
    }
    
    /**
     * Get thumbnail path
     *
     * @param string $originalPath
     * @param string $extension
     * @return string
     */
    private function getThumbnailPath(string $originalPath, string $extension = 'webp'): string
    {
        $pathInfo = pathinfo($originalPath);
        return $pathInfo['dirname'] . '/thumbnails/' . $pathInfo['filename'] . '_thumb.' . $extension;
    }
    
    /**
     * Get medium path
     *
     * @param string $originalPath
     * @param string $extension
     * @return string
     */
    private function getMediumPath(string $originalPath, string $extension = 'webp'): string
    {
        $pathInfo = pathinfo($originalPath);
        return $pathInfo['dirname'] . '/medium/' . $pathInfo['filename'] . '_medium.' . $extension;
    }
    
    /**
     * Get maximum file size for media type
     *
     * @param string $mediaType
     * @return int
     */
    private function getMaxFileSize(string $mediaType): int
    {
        switch ($mediaType) {
            case self::TYPE_IMAGE:
                return self::MAX_IMAGE_SIZE;
            case self::TYPE_VIDEO:
                return self::MAX_VIDEO_SIZE;
            case self::TYPE_AUDIO:
            case self::TYPE_VOICE:
                return self::MAX_AUDIO_SIZE;
            default:
                return self::MAX_FILE_SIZE;
        }
    }
    
    /**
     * Get allowed MIME types for media type
     *
     * @param string $mediaType
     * @return array
     */
    private function getAllowedMimeTypes(string $mediaType): array
    {
        switch ($mediaType) {
            case self::TYPE_IMAGE:
                return self::ALLOWED_IMAGE_TYPES;
            case self::TYPE_VIDEO:
                return self::ALLOWED_VIDEO_TYPES;
            case self::TYPE_AUDIO:
            case self::TYPE_VOICE:
                return self::ALLOWED_AUDIO_TYPES;
            default:
                return ['*/*']; // Allow all types for general files
        }
    }
    
    /**
     * Format bytes to human readable format
     *
     * @param int $bytes
     * @return string
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Delete media files from S3
     *
     * @param array $filePaths
     * @return bool
     */
    public function deleteMediaFiles(array $filePaths): bool
    {
        try {
            $deletedFiles = [];
            $failedFiles = [];
            
            foreach ($filePaths as $path) {
                if (empty($path) || !is_string($path)) {
                    Log::warning("Invalid S3 path provided for deletion: " . json_encode($path));
                    continue;
                }
                
                try {
                    if (Storage::disk('s3')->exists($path)) {
                        Storage::disk('s3')->delete($path);
                        $deletedFiles[] = $path;
                        Log::info("Successfully deleted S3 file: " . $path);
                    } else {
                        Log::warning("S3 file not found for deletion: " . $path);
                    }
                } catch (Exception $e) {
                    $failedFiles[] = $path;
                    Log::error("Failed to delete S3 file: " . $path . " - " . $e->getMessage());
                }
            }
            
            if (!empty($failedFiles)) {
                Log::warning("Some files failed to delete from S3: " . implode(', ', $failedFiles));
                return false;
            }
            
            return true;
        } catch (Exception $e) {
            Log::error("Failed to delete media files from S3: " . $e->getMessage(), [
                'file_paths' => $filePaths,
                'exception' => $e
            ]);
            return false;
        }
    }
    
    /**
     * Get media URL with appropriate size
     *
     * @param string $originalUrl
     * @param string $mediumUrl
     * @param string $thumbnailUrl
     * @param string $size
     * @return string
     */
    public function getMediaUrl(string $originalUrl, string $mediumUrl = null, string $thumbnailUrl = null, string $size = 'medium'): string
    {
        switch ($size) {
            case 'thumbnail':
                return $thumbnailUrl ?: $originalUrl;
            case 'original':
                return $originalUrl;
            case 'medium':
            default:
                return $mediumUrl ?: $originalUrl;
        }
    }
} 