# Media Upload System Documentation

## Overview

The media upload system has been optimized to use AWS S3 for all media storage with advanced media conversion capabilities using FFmpeg. The system automatically converts media files to optimized formats before uploading:

- **Images**: Converted to WebP format with multiple sizes
- **Videos**: Converted to MP4 format with thumbnail generation  
- **Audio/Voice**: M4A files converted to OGG format for better compression
- **All media**: Organized storage structure in AWS S3

## Architecture

### Services

1. **MediaUploadService** - Core service with FFmpeg integration for media conversion
2. **ImageProcessingService** - WebP conversion and multi-size generation
3. **AuthService** - Updated to use MediaUploadService for profile photos
4. **ChatController** - Updated to use MediaUploadService for chat media

### Storage

- **Primary Storage**: AWS S3
- **Fallback**: Local storage (for development)
- **CDN**: CloudFront (recommended for production)

### Media Conversion Pipeline

Using [PHP-FFMpeg](https://github.com/PHP-FFMpeg/PHP-FFMpeg) for robust media processing:

```
Input File → Validation → FFmpeg Conversion → S3 Upload → Cleanup
```

## MediaUploadService

### Enhanced Features

- **FFmpeg Integration**: Professional media conversion using PHP-FFMpeg
- **Format Optimization**: 
  - Images → WebP (better compression, quality)
  - Videos → MP4 (universal compatibility)  
  - Voice/M4A → OGG (optimized for voice)
- **Multi-size Generation**: Automatic thumbnail and medium size creation
- **Video Thumbnails**: Automatic thumbnail extraction from videos
- **Quality Control**: Configurable quality settings per format
- **Temporary File Management**: Secure temp file handling with cleanup

### Media Types & Conversion

| Input Type | Output Format | Sizes Generated | Special Processing |
|------------|---------------|-----------------|-------------------|
| **Images** | WebP | Original, Medium (300x300), Thumb (50x50) | Orientation correction, quality optimization |
| **Videos** | MP4 | Original + WebP thumbnail | H.264 encoding, automatic thumbnail at 1s |
| **Voice/M4A** | OGG | Original | Optimized for voice compression (96kbps) |
| **Audio** | Original/OGG | Original | Duration extraction, optional OGG conversion |
| **Files** | Original | Original | Direct upload |

### Configuration

#### Environment Variables

```env
# FFmpeg Configuration
FFMPEG_BINARY=/usr/bin/ffmpeg
FFPROBE_BINARY=/usr/bin/ffprobe

# AWS Configuration
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name
AWS_URL=https://your-bucket.s3.amazonaws.com
```

#### FFmpeg Installation

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install ffmpeg

# CentOS/RHEL
sudo yum install epel-release
sudo yum install ffmpeg

# macOS
brew install ffmpeg

# Verify installation
ffmpeg -version
ffprobe -version
```

### Usage Examples

#### Image Upload with WebP Conversion
```php
$uploadResult = $mediaUploadService->uploadMedia(
    $file, 
    'profile_photos', 
    $userId, 
    ['is_profile_photo' => true]
);

// Result contains WebP URLs:
// - original_url (WebP)
// - medium_url (WebP, 300x300)  
// - thumbnail_url (WebP, 50x50)
// - metadata with original format info
```

#### Voice Message Upload (M4A → OGG)
```php
$uploadResult = $mediaUploadService->uploadMedia(
    $m4aFile,
    'chat',
    $userId,
    [
        'is_voice_message' => true,
        'duration' => 120 // seconds
    ]
);

// Automatically converts M4A to OGG
// Result: optimized OGG file with duration metadata
```

#### Video Upload with MP4 Conversion
```php
$uploadResult = $mediaUploadService->uploadMedia(
    $videoFile,
    'chat',
    $userId,
    ['generate_thumbnail' => true]
);

// Converts video to MP4 + generates WebP thumbnail
// Result: MP4 video + thumbnail at 1-second mark
```

## Advanced Media Processing

### Image Processing with WebP

**Benefits of WebP:**
- 25-35% better compression than JPEG
- Supports transparency (like PNG)
- Supported by all modern browsers
- Maintains quality with smaller file sizes

**Processing Pipeline:**
1. **Input Validation**: MIME type, file size checks
2. **EXIF Orientation**: Automatic rotation correction
3. **Size Optimization**: Resize if larger than max dimensions
4. **Multi-size Generation**: 
   - Thumbnail: 50x50px (square crop)
   - Medium: 300x300px (maintain aspect ratio)
   - Original: Up to 1920px (maintain aspect ratio)
5. **WebP Conversion**: Quality-optimized encoding
6. **S3 Upload**: Organized directory structure

### Video Processing with FFmpeg

**Conversion Settings:**
```php
// MP4 format with optimized settings
$format = new X264('libmp3lame', 'libx264');
$format->setKiloBitrate(1000)      // Video bitrate
       ->setAudioChannels(2)        // Stereo audio
       ->setAudioKiloBitrate(128);  // Audio bitrate
```

**Features:**
- Universal MP4 compatibility
- Automatic thumbnail generation
- Video metadata extraction (duration, dimensions)
- Optimized compression settings

### Audio Processing

**Voice Message Optimization:**
- **Input**: M4A, MP3, WAV, AAC files
- **Output**: OGG Vorbis format (96kbps)
- **Benefits**: Better compression for voice, smaller file sizes
- **Duration**: Automatic extraction and validation

**Regular Audio:**
- Preserves original format for music/high-quality audio
- Optional conversion to OGG for voice messages
- Duration metadata extraction

## S3 Storage Structure

```
media/
├── profile_photos/
│   └── {user_id}/
│       ├── profile_1_1234567890_abc123.webp
│       ├── thumbnails/
│       │   └── profile_1_1234567890_abc123_thumb.webp
│       └── medium/
│           └── profile_1_1234567890_abc123_medium.webp
├── chat/
│   └── {user_id}/
│       ├── chat_1_1234567890_abc123.mp4
│       ├── chat_1_1234567890_def456.ogg
│       ├── chat_1_1234567890_ghi789.webp
│       └── thumbnails/
│           └── thumb_1234567890_xyz.webp
└── stories/
    └── {user_id}/
```

## Performance Optimizations

### FFmpeg Processing

- **Parallel Processing**: Multi-threaded FFmpeg encoding
- **Temporary Files**: Secure temp file management with auto-cleanup
- **Memory Management**: Streaming processing for large files
- **Error Recovery**: Graceful fallback for conversion failures

### S3 Optimizations

- **Direct Upload**: Processed files uploaded directly to S3
- **Batch Operations**: Multiple sizes uploaded efficiently
- **CDN Ready**: Optimized for CloudFront distribution
- **Compression**: WebP and OGG provide significant size savings

## Error Handling

### Conversion Errors

```php
try {
    $result = $mediaUploadService->uploadMedia($file, 'chat', $userId);
} catch (Exception $e) {
    // Handles FFmpeg errors, S3 failures, validation issues
    Log::error('Upload failed: ' . $e->getMessage());
}
```

### Common Issues

1. **FFmpeg Not Available**
   ```
   FFMpeg is not available for video processing
   ```

2. **Conversion Failed**
   ```
   Video conversion failed: Invalid codec
   ```

3. **File Size Limits**
   ```
   File size exceeds maximum allowed size of 100 MB
   ```

### Fallback Behavior

- If FFmpeg fails: Falls back to direct upload
- If conversion fails: Uploads original file
- If S3 fails: Comprehensive error logging

## API Response Examples

### Image Upload Response
```json
{
  "original_url": "https://s3.amazonaws.com/bucket/media/profile_photos/1/profile_1_1234567890_abc123.webp",
  "medium_url": "https://s3.amazonaws.com/bucket/media/profile_photos/1/medium/profile_1_1234567890_abc123_medium.webp",
  "thumbnail_url": "https://s3.amazonaws.com/bucket/media/profile_photos/1/thumbnails/profile_1_1234567890_abc123_thumb.webp",
  "media_type": "image",
  "metadata": {
    "original_width": 2048,
    "original_height": 1536,
    "processed_width": 1920,
    "processed_height": 1440,
    "file_size": 2048000,
    "original_mime_type": "image/jpeg",
    "converted_format": "webp",
    "processing_timestamp": "2024-01-01T12:00:00.000Z"
  }
}
```

### Voice Message Response
```json
{
  "original_url": "https://s3.amazonaws.com/bucket/media/chat/1/chat_1_1234567890_abc123.ogg",
  "media_type": "voice",
  "metadata": {
    "file_size": 512000,
    "original_mime_type": "audio/m4a",
    "converted_format": "ogg",
    "duration": 45.2,
    "is_voice_message": true,
    "upload_timestamp": "2024-01-01T12:00:00.000Z"
  }
}
```

### Video Upload Response
```json
{
  "original_url": "https://s3.amazonaws.com/bucket/media/chat/1/chat_1_1234567890_abc123.mp4",
  "thumbnail_url": "https://s3.amazonaws.com/bucket/media/chat/1/thumbnails/thumb_1234567890_xyz.webp",
  "media_type": "video",
  "metadata": {
    "file_size": 15360000,
    "original_mime_type": "video/mov",
    "converted_format": "mp4",
    "duration": 30.5,
    "width": 1280,
    "height": 720,
    "upload_timestamp": "2024-01-01T12:00:00.000Z"
  }
}
```

## Security Considerations

### File Validation

- **Strict MIME Type Checking**: Validates actual file content
- **Size Limits**: Configurable per media type
- **Format Validation**: FFmpeg validates media integrity
- **Temporary File Security**: Secure temp file handling

### FFmpeg Security

- **Path Sanitization**: Prevents path traversal attacks
- **Resource Limits**: Timeout and thread limitations
- **Error Isolation**: Conversion errors don't affect system

## Installation Requirements

### Composer Dependencies

```json
{
  "require": {
    "php-ffmpeg/php-ffmpeg": "^1.0",
    "intervention/image": "^2.7",
    "aws/aws-sdk-php": "^3.0"
  }
}
```

### System Requirements

- **PHP**: 8.1+ with GD or Imagick extension
- **FFmpeg**: 4.0+ with libx264, libmp3lame, libvorbis
- **Storage**: AWS S3 or compatible service
- **Memory**: 512MB+ recommended for video processing

## Monitoring and Metrics

### Key Performance Indicators

- **Conversion Success Rate**: FFmpeg processing success
- **Processing Time**: Average conversion duration
- **File Size Reduction**: Compression efficiency
- **Error Rates**: By media type and conversion
- **S3 Upload Success**: Storage reliability

### Logging

```php
Log::info('Media conversion started', [
    'user_id' => $userId,
    'original_format' => $file->getMimeType(),
    'target_format' => $targetFormat,
    'file_size' => $file->getSize()
]);
```

## Troubleshooting

### FFmpeg Issues

1. **Binary Not Found**
   ```bash
   which ffmpeg
   which ffprobe
   ```

2. **Permission Issues**
   ```bash
   chmod +x /usr/bin/ffmpeg
   chmod +x /usr/bin/ffprobe
   ```

3. **Codec Missing**
   ```bash
   ffmpeg -codecs | grep libx264
   ```

### Performance Tuning

1. **Increase PHP Memory**
   ```ini
   memory_limit = 1G
   max_execution_time = 300
   ```

2. **FFmpeg Threads**
   ```php
   'ffmpeg.threads' => 4  // Adjust based on CPU cores
   ```

3. **Temporary Directory**
   ```php
   sys_get_temp_dir()  // Ensure sufficient space
   ```

This enhanced media upload system provides professional-grade media processing with optimal format conversion, ensuring the best user experience while maintaining high performance and reliability. 