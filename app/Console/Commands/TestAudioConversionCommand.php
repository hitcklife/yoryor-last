<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MediaUploadService;
use Illuminate\Http\UploadedFile;

class TestAudioConversionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:audio-conversion {--file= : Path to test audio file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test audio conversion functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Audio Conversion Test ===');

        $mediaService = new MediaUploadService();

        // Check FFmpeg availability
        $this->info('FFmpeg Status:');
        
        // Use reflection to access protected property
        $reflection = new \ReflectionClass($mediaService);
        $ffmpegProperty = $reflection->getProperty('ffmpeg');
        $ffmpegProperty->setAccessible(true);
        $ffmpeg = $ffmpegProperty->getValue($mediaService);
        
        if ($ffmpeg) {
            $this->info('✓ FFmpeg is available');
            
            // Test FFmpeg version using system command
            try {
                $output = shell_exec("ffmpeg -version 2>&1");
                $this->info('FFmpeg version: ' . substr($output, 0, 50) . '...');
            } catch (\Exception $e) {
                $this->error('✗ FFmpeg version check failed: ' . $e->getMessage());
            }
        } else {
            $this->error('✗ FFmpeg is not available');
        }

        $this->newLine();

        // Test file path
        $testFile = $this->option('file');
        
        if (!$testFile) {
            // Create a test M4A file (simulate the voice message)
            $testFile = tempnam(sys_get_temp_dir(), 'test_audio_') . '.m4a';
            
            // Create a minimal M4A file header (this is just for testing the process)
            $m4aHeader = "\x00\x00\x00\x20ftypM4A \x00\x00\x00\x00M4A mp42isom";
            file_put_contents($testFile, $m4aHeader . 'test audio content');
            
            $this->info("Created test file: $testFile");
        } else {
            if (!file_exists($testFile)) {
                $this->error("Test file not found: $testFile");
                return 1;
            }
            $this->info("Using test file: $testFile");
        }

        // Create UploadedFile mock
        $uploadedFile = new UploadedFile(
            $testFile,
            'voice-message.m4a',
            'audio/m4a',
            null,
            true
        );

        $this->info('UploadedFile created:');
        $this->info('- Original name: ' . $uploadedFile->getClientOriginalName());
        $this->info('- MIME type: ' . $uploadedFile->getMimeType());
        $this->info('- Size: ' . $uploadedFile->getSize() . ' bytes');

        $this->newLine();

        $this->info('Testing MediaUploadService:');

        try {
            $options = [
                'is_voice_message' => true,
                'duration' => 3
            ];
            
            $this->info('Testing with options: ' . json_encode($options));
            
            $result = $mediaService->uploadMedia($uploadedFile, 'chat', 1, $options);
            
            $this->info('✓ Upload successful!');
            $this->info('Result:');
            $this->info('- Media type: ' . $result['media_type']);
            $this->info('- Original URL: ' . $result['original_url']);
            $this->info('- S3 Path: ' . $result['file_paths']['original']);
            $this->info('- Metadata: ' . json_encode($result['metadata'], JSON_PRETTY_PRINT));
            
        } catch (\Exception $e) {
            $this->error('✗ Upload failed: ' . $e->getMessage());
            $this->error('Stack trace:');
            $this->error($e->getTraceAsString());
            return 1;
        }

        // Cleanup if we created the test file
        if (!$this->option('file') && file_exists($testFile)) {
            unlink($testFile);
        }

        $this->newLine();
        $this->info('=== Test Complete ===');
        
        return 0;
    }
} 