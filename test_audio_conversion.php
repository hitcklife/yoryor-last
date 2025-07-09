<?php

require_once 'vendor/autoload.php';

use App\Services\MediaUploadService;
use Illuminate\Http\UploadedFile;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Audio Conversion Test ===\n\n";

// Test the MediaUploadService
$mediaService = new MediaUploadService();

// Check FFmpeg availability
echo "FFmpeg Status:\n";
if ($mediaService->ffmpeg) {
    echo "✓ FFmpeg is available\n";
    
    // Test FFmpeg binary
    $ffmpegPath = env('FFMPEG_BINARY', '/usr/bin/ffmpeg');
    $ffprobePath = env('FFPROBE_BINARY', '/usr/bin/ffprobe');
    
    echo "FFmpeg binary: " . (file_exists($ffmpegPath) ? "✓ Found at $ffmpegPath" : "✗ Not found at $ffmpegPath") . "\n";
    echo "FFprobe binary: " . (file_exists($ffprobePath) ? "✓ Found at $ffprobePath" : "✗ Not found at $ffprobePath") . "\n";
    
    // Test FFmpeg version
    try {
        $output = shell_exec("$ffmpegPath -version 2>&1");
        echo "FFmpeg version: " . substr($output, 0, 50) . "...\n";
    } catch (Exception $e) {
        echo "✗ FFmpeg version check failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "✗ FFmpeg is not available\n";
}

echo "\n=== Test File Generation ===\n";

// Create a test M4A file (simulate the voice message)
$testFile = tempnam(sys_get_temp_dir(), 'test_audio_') . '.m4a';

// Create a simple test file (this won't be a real audio file, but it will test the process)
file_put_contents($testFile, 'test audio content');

echo "Created test file: $testFile\n";

// Create UploadedFile mock
$uploadedFile = new UploadedFile(
    $testFile,
    'voice-message.m4a',
    'audio/m4a',
    null,
    true
);

echo "UploadedFile created:\n";
echo "- Original name: " . $uploadedFile->getClientOriginalName() . "\n";
echo "- MIME type: " . $uploadedFile->getMimeType() . "\n";
echo "- Size: " . $uploadedFile->getSize() . " bytes\n";

echo "\n=== Testing MediaUploadService ===\n";

try {
    $options = [
        'is_voice_message' => true,
        'duration' => 3
    ];
    
    echo "Testing with options: " . json_encode($options) . "\n";
    
    $result = $mediaService->uploadMedia($uploadedFile, 'chat', 1, $options);
    
    echo "✓ Upload successful!\n";
    echo "Result:\n";
    echo "- Media type: " . $result['media_type'] . "\n";
    echo "- Original URL: " . $result['original_url'] . "\n";
    echo "- S3 Path: " . $result['file_paths']['original'] . "\n";
    echo "- Metadata: " . json_encode($result['metadata'], JSON_PRETTY_PRINT) . "\n";
    
} catch (Exception $e) {
    echo "✗ Upload failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// Cleanup
if (file_exists($testFile)) {
    unlink($testFile);
}

echo "\n=== Test Complete ===\n"; 