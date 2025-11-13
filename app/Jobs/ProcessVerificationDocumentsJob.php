<?php

namespace App\Jobs;

use App\Models\VerificationRequest;
use App\Services\MediaUploadService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessVerificationDocumentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $verificationRequest;
    protected $uploadedFiles;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public $timeout = 300; // 5 minutes for document processing

    /**
     * Create a new job instance.
     */
    public function __construct(VerificationRequest $verificationRequest, array $uploadedFiles)
    {
        $this->verificationRequest = $verificationRequest;
        $this->uploadedFiles = $uploadedFiles;
        
        // Set to documents queue for processing
        $this->onQueue('documents');
    }

    /**
     * Execute the job.
     */
    public function handle(MediaUploadService $mediaUploadService): void
    {
        try {
            Log::info('Processing verification documents', [
                'verification_id' => $this->verificationRequest->id,
                'user_id' => $this->verificationRequest->user_id,
                'verification_type' => $this->verificationRequest->verification_type,
                'files_count' => count($this->uploadedFiles)
            ]);

            $processedDocuments = [];
            $documentMetadata = [];

            foreach ($this->uploadedFiles as $file) {
                $processed = $this->processDocument($file, $mediaUploadService);
                $processedDocuments[] = $processed;
                
                // Extract metadata for analysis
                $metadata = $this->extractDocumentMetadata($processed);
                $documentMetadata[] = $metadata;
            }

            // Perform automated validation checks
            $validationResults = $this->performAutomatedValidation($documentMetadata);

            // Update verification request with processed documents
            $this->verificationRequest->update([
                'documents' => $processedDocuments,
                'document_metadata' => $documentMetadata,
                'automated_validation' => $validationResults,
                'processing_completed_at' => now(),
                'status' => $validationResults['requires_manual_review'] ? 'pending_review' : 'auto_validated'
            ]);

            // If auto-validation passed, mark as approved
            if (!$validationResults['requires_manual_review'] && $validationResults['confidence_score'] > 0.8) {
                $this->verificationRequest->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approval_method' => 'automated'
                ]);
            }

            Log::info('Verification documents processed successfully', [
                'verification_id' => $this->verificationRequest->id,
                'documents_processed' => count($processedDocuments),
                'validation_score' => $validationResults['confidence_score'],
                'requires_review' => $validationResults['requires_manual_review']
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process verification documents', [
                'verification_id' => $this->verificationRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Mark verification as failed
            $this->verificationRequest->update([
                'status' => 'processing_failed',
                'error_message' => $e->getMessage(),
                'failed_at' => now()
            ]);

            throw $e;
        }
    }

    /**
     * Process individual document
     */
    private function processDocument(array $fileData, MediaUploadService $mediaUploadService): array
    {
        try {
            // Upload document to secure storage
            $uploadResult = $mediaUploadService->uploadMedia(
                $fileData['file'],
                'verification_documents',
                $this->verificationRequest->user_id,
                [
                    'context' => 'identity_verification',
                    'verification_type' => $this->verificationRequest->verification_type,
                    'encrypt' => true, // Encrypt sensitive documents
                    'optimize' => false, // Don't compress identity documents
                ]
            );

            // Remove EXIF data for privacy
            $this->removeExifData($uploadResult['original_url']);

            // Generate secure thumbnail if it's an image
            $thumbnailUrl = null;
            if ($this->isImageFile($fileData['file'])) {
                $thumbnailUrl = $this->generateSecureThumbnail($uploadResult['original_url'], $mediaUploadService);
            }

            return [
                'original_filename' => $fileData['file']->getClientOriginalName(),
                'secure_url' => $uploadResult['original_url'],
                'thumbnail_url' => $thumbnailUrl,
                'file_type' => $fileData['file']->getMimeType(),
                'file_size' => $fileData['file']->getSize(),
                'processed_at' => now(),
                'metadata' => $uploadResult['metadata'] ?? []
            ];

        } catch (\Exception $e) {
            Log::error('Failed to process document', [
                'verification_id' => $this->verificationRequest->id,
                'filename' => $fileData['file']->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Extract metadata from processed document
     */
    private function extractDocumentMetadata(array $processedDocument): array
    {
        $metadata = [
            'file_type' => $processedDocument['file_type'],
            'file_size' => $processedDocument['file_size'],
            'image_quality' => null,
            'text_detected' => false,
            'face_detected' => false,
            'document_type_detected' => null,
            'confidence_indicators' => []
        ];

        // Basic image quality analysis for photos
        if ($this->isImageType($processedDocument['file_type'])) {
            $metadata['image_quality'] = $this->analyzeImageQuality($processedDocument['secure_url']);
            
            // Simple text detection (check if document contains text-like patterns)
            $metadata['text_detected'] = $this->hasTextPatterns($processedDocument['secure_url']);
            
            // Basic face detection for ID documents
            if (in_array($this->verificationRequest->verification_type, ['identity', 'photo'])) {
                $metadata['face_detected'] = $this->hasFacePatterns($processedDocument['secure_url']);
            }
        }

        return $metadata;
    }

    /**
     * Perform automated validation on documents
     */
    private function performAutomatedValidation(array $documentMetadata): array
    {
        $validation = [
            'confidence_score' => 0.0,
            'requires_manual_review' => true,
            'validation_checks' => [],
            'flags' => []
        ];

        $totalScore = 0;
        $checkCount = 0;

        foreach ($documentMetadata as $doc) {
            // Check file type appropriateness
            if ($this->isAppropriateFileType($doc['file_type'])) {
                $validation['validation_checks'][] = 'appropriate_file_type';
                $totalScore += 0.2;
            } else {
                $validation['flags'][] = 'inappropriate_file_type';
            }
            $checkCount++;

            // Check image quality
            if (isset($doc['image_quality']) && $doc['image_quality'] >= 0.7) {
                $validation['validation_checks'][] = 'good_image_quality';
                $totalScore += 0.3;
            } elseif (isset($doc['image_quality'])) {
                $validation['flags'][] = 'poor_image_quality';
            }
            $checkCount++;

            // Check for text in documents (good for IDs)
            if ($this->verificationRequest->verification_type === 'identity' && $doc['text_detected']) {
                $validation['validation_checks'][] = 'text_detected_in_id';
                $totalScore += 0.2;
            }
            $checkCount++;

            // Check for face in photo verification
            if ($this->verificationRequest->verification_type === 'photo' && $doc['face_detected']) {
                $validation['validation_checks'][] = 'face_detected_in_photo';
                $totalScore += 0.3;
            }
            $checkCount++;
        }

        $validation['confidence_score'] = $checkCount > 0 ? $totalScore / $checkCount : 0;

        // Determine if manual review is needed
        $validation['requires_manual_review'] = 
            $validation['confidence_score'] < 0.7 || 
            !empty($validation['flags']) ||
            in_array($this->verificationRequest->verification_type, ['identity', 'background_check']);

        return $validation;
    }

    /**
     * Remove EXIF data from uploaded images
     */
    private function removeExifData(string $filePath): void
    {
        try {
            if (function_exists('exif_read_data')) {
                // Basic EXIF removal - in production, use more sophisticated methods
                $image = imagecreatefromstring(Storage::get($filePath));
                if ($image) {
                    // Re-save without EXIF data
                    Storage::put($filePath, '');
                    imagejpeg($image, Storage::path($filePath), 95);
                    imagedestroy($image);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to remove EXIF data', [
                'file_path' => $filePath,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generate secure thumbnail for document preview
     */
    private function generateSecureThumbnail(string $originalUrl, MediaUploadService $mediaUploadService): ?string
    {
        try {
            // Generate a blurred/watermarked thumbnail for security
            return $mediaUploadService->generateSecureThumbnail($originalUrl, [
                'width' => 300,
                'height' => 200,
                'blur' => 2, // Add slight blur for security
                'watermark' => 'VERIFICATION DOCUMENT'
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to generate secure thumbnail', [
                'original_url' => $originalUrl,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Check if file is an image
     */
    private function isImageFile($file): bool
    {
        return str_starts_with($file->getMimeType(), 'image/');
    }

    /**
     * Check if file type is image
     */
    private function isImageType(string $mimeType): bool
    {
        return str_starts_with($mimeType, 'image/');
    }

    /**
     * Check if file type is appropriate for verification
     */
    private function isAppropriateFileType(string $mimeType): bool
    {
        $allowedTypes = [
            'image/jpeg',
            'image/png', 
            'image/webp',
            'application/pdf'
        ];

        return in_array($mimeType, $allowedTypes);
    }

    /**
     * Analyze image quality (basic implementation)
     */
    private function analyzeImageQuality(string $filePath): float
    {
        try {
            // Basic quality analysis - in production, use more sophisticated methods
            $imageInfo = getimagesize(Storage::path($filePath));
            if (!$imageInfo) return 0.0;

            $width = $imageInfo[0];
            $height = $imageInfo[1];
            $minDimension = min($width, $height);

            // Score based on resolution
            if ($minDimension >= 1000) return 1.0;
            if ($minDimension >= 800) return 0.8;
            if ($minDimension >= 600) return 0.6;
            if ($minDimension >= 400) return 0.4;
            return 0.2;

        } catch (\Exception $e) {
            return 0.0;
        }
    }

    /**
     * Check for text patterns in image (basic implementation)
     */
    private function hasTextPatterns(string $filePath): bool
    {
        // Placeholder for OCR or text detection
        // In production, integrate with OCR service like AWS Textract, Google Vision API
        return true; // Assume documents have text
    }

    /**
     * Check for face patterns in image (basic implementation)
     */
    private function hasFacePatterns(string $filePath): bool
    {
        // Placeholder for face detection
        // In production, integrate with face detection service
        return true; // Assume photos have faces
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Verification document processing job failed permanently', [
            'verification_id' => $this->verificationRequest->id,
            'attempts' => $this->attempts,
            'error' => $exception->getMessage()
        ]);

        $this->verificationRequest->update([
            'status' => 'processing_failed',
            'error_message' => $exception->getMessage(),
            'failed_at' => now()
        ]);
    }
}